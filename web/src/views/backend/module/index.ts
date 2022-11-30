import { state } from './store'
import { index, modules, info, createOrder, payOrder, postInstallModule, getInstallState, changeState, payCheck } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { ElNotification } from 'element-plus'
import { useTerminal } from '/@/stores/terminal'
import { taskStatus } from '/@/components/terminal/constant'
import { moduleInstallState, moduleState } from './types'
import { uuid } from '/@/utils/random'
import { fullUrl } from '/@/utils/common'

export const loadData = () => {
    state.loading.table = true
    if (!state.table.indexLoaded) {
        loadIndex().then(() => {
            getModules()
        })
    } else {
        getModules()
    }
}

export const onRefreshTableData = () => {
    state.table.indexLoaded = false
    for (const key in state.table.modulesEbak) {
        state.table.modulesEbak[key] = undefined
    }
    loadData()
}

const loadIndex = () => {
    return index().then((res) => {
        state.table.indexLoaded = true
        state.installedModule = res.data.installed
        const installedModuleUids = []
        if (res.data.installed) {
            for (const key in res.data.installed) {
                installedModuleUids.push(res.data.installed[key].uid)
            }
            state.installedModuleUids = installedModuleUids
        }
    })
}

const getModules = () => {
    if (typeof state.table.modulesEbak[state.table.params.activeTab] != 'undefined') {
        state.table.modules[state.table.params.activeTab] = modulesOnlyLocalHandle(state.table.modulesEbak[state.table.params.activeTab])
        state.loading.table = false
        return
    }
    const params: anyObj = {}
    for (const key in state.table.params) {
        if (state.table.params[key] != '') {
            params[key] = state.table.params[key]
        }
    }
    const moduleUids: string[] = []
    const installedModule: { uid: string; version: string }[] = []
    state.installedModule.forEach((item) => {
        installedModule.push({
            uid: item.uid,
            version: item.version,
        })
    })
    params['installed'] = installedModule
    modules(params)
        .then((res) => {
            if (params.activeTab == 'all') {
                res.data.rows.forEach((item: anyObj) => {
                    moduleUids.push(item.uid)
                })

                state.installedModule.forEach((item) => {
                    if (moduleUids.indexOf(item.uid) === -1) {
                        res.data.rows.push(item)
                    }
                })
            }

            state.table.remark = res.data.remark
            state.table.modulesEbak[params.activeTab] = res.data.rows.map((item: anyObj) => {
                const idx = state.installedModuleUids.indexOf(item.uid)
                if (idx !== -1) {
                    item.state = state.installedModule[idx].state
                    item.version = state.installedModule[idx].version
                    item.website = state.installedModule[idx].website
                    item.stateTag = moduleStatus(item.state)
                } else {
                    item.state = 0
                }

                if (item.new_version && item.tags) {
                    item.tags.push({
                        name: '有新版本',
                        type: 'danger',
                    })
                }

                return item
            })
            state.table.modules[params.activeTab] = modulesOnlyLocalHandle(state.table.modulesEbak[params.activeTab])
            state.table.category = res.data.category
        })
        .finally(() => {
            state.loading.table = false
        })
}

export const showInfo = (uid: string) => {
    state.dialog.goodsInfo = true
    state.loading.goodsInfo = true

    const localItem = state.installedModule.find((item) => {
        return item.uid == uid
    })

    info({ uid: uid, localVersion: localItem?.version })
        .then((res) => {
            if (localItem) {
                if (res.data.info.type == 'local') {
                    res.data.info = localItem
                    res.data.info.images = [fullUrl('/static/images/local-module-logo.png')]
                    res.data.info.type = 'local' // 纯本地模块
                } else {
                    res.data.info.type = 'online'
                    res.data.info.state = localItem.state
                    res.data.info.version = localItem.version
                }
                res.data.info.enable = localItem.state === moduleInstallState.DISABLE ? false : true
            } else {
                res.data.info.state = 0
                res.data.info.type = 'online'
            }
            state.goodsInfo = res.data.info
        })
        .catch((err) => {
            if (loginExpired(err)) {
                state.dialog.goodsInfo = false
            }
        })
        .finally(() => {
            state.loading.goodsInfo = false
        })
}

export const onBuy = () => {
    state.dialog.buy = true
    state.loading.buy = true
    createOrder({
        goods_id: state.goodsInfo.id,
    })
        .then((res) => {
            state.loading.buy = false
            state.buy.info = res.data.info
        })
        .catch((err) => {
            state.dialog.buy = false
            state.loading.buy = false
            loginExpired(err)
        })
}

export const onPay = (payType: 'score' | 'wx' | 'balance') => {
    state.loading.common = true
    payOrder(state.buy.info.id, payType)
        .then((res) => {
            if (payType == 'wx') {
                // 关闭其他弹窗
                state.dialog.buy = false
                state.dialog.goodsInfo = false

                // 显示支付二维码
                state.dialog.pay = true
                state.payInfo = res.data

                // 轮询获取支付状态
                const timer = setInterval(() => {
                    payCheck(state.payInfo.info.sn)
                        .then(() => {
                            state.payInfo.pay.status = 'success'
                            clearInterval(timer)
                            onInstall(res.data.info.uid, res.data.info.id)
                            state.dialog.pay = false
                        })
                        .catch(() => {})
                }, 3000)
            } else {
                onInstall(res.data.info.uid, res.data.info.id)
            }
        })
        .catch((err) => {
            loginExpired(err)
        })
        .finally(() => {
            state.loading.common = false
        })
}

export const showCommonLoading = (loadingTitle: moduleState['common']['loadingTitle']) => {
    state.common.type = 'loading'
    state.common.loadingTitle = loadingTitle
    state.common.loadingComponentKey = uuid()
}

export const onInstall = (uid: string, id: number) => {
    state.dialog.common = true
    showCommonLoading('init')
    state.common.dialogTitle = '安装'

    // 获取安装状态
    getInstallState(uid).then((res) => {
        if (
            res.data.state === moduleInstallState.INSTALLED ||
            res.data.state === moduleInstallState.DISABLE ||
            res.data.state === moduleInstallState.DIRECTORY_OCCUPIED
        ) {
            ElNotification({
                type: 'error',
                message:
                    res.data.state === moduleInstallState.INSTALLED || res.data.state === moduleInstallState.DISABLE
                        ? '安装取消，因为模块已经存在！'
                        : '安装取消，因为模块所需目录被占用！',
            })
            state.dialog.common = false
        } else {
            showCommonLoading(res.data.state === moduleInstallState.UNINSTALLED ? 'download' : 'install')
            execInstall(uid, id)

            // 关闭其他弹窗
            state.dialog.baAccount = false
            state.dialog.buy = false
            state.dialog.goodsInfo = false
        }
    })
}

export const execInstall = (uid: string, id: number, extend: anyObj = {}) => {
    state.common.disableHmr = true
    postInstallModule(uid, id, extend)
        .then(() => {
            state.common.dialogTitle = '安装完成'
            state.common.moduleState = moduleInstallState.INSTALLED
            state.common.type = 'done'
        })
        .catch((res) => {
            if (loginExpired(res)) return
            if (res.code == -1) {
                state.common.uid = res.data.uid
                state.common.type = 'InstallConflict'
                state.common.dialogTitle = '发现冲突，请手动处理'
                state.common.fileConflict = res.data.fileConflict
                state.common.dependConflict = res.data.dependConflict
            } else if (res.code == -2) {
                state.common.type = 'done'
                state.common.uid = res.data.uid
                state.common.dialogTitle = '等待依赖安装'
                state.common.moduleState = moduleInstallState.DEPENDENT_WAIT_INSTALL
                state.common.waitInstallDepend = res.data.wait_install
                state.common.dependInstallState = 'executing'
                const terminal = useTerminal()
                if (res.data.wait_install.includes('npm_dependent_wait_install')) {
                    terminal.addTaskPM('web-install', true, 'module-install:' + res.data.uid, (res: number) => {
                        terminalTaskExecComplete(res, 'npm_dependent_wait_install')
                    })
                }
                if (res.data.wait_install.includes('composer_dependent_wait_install')) {
                    terminal.addTask('composer.update', true, 'module-install:' + res.data.uid, (res: number) => {
                        terminalTaskExecComplete(res, 'composer_dependent_wait_install')
                    })
                }
            } else if (res.code == 0) {
                ElNotification({
                    type: 'error',
                    message: res.msg,
                })
                state.dialog.common = false
            }
        })
        .finally(() => {
            state.loading.common = false
            state.common.disableHmr = true
            onRefreshTableData()
        })
}

const terminalTaskExecComplete = (res: number, type: string) => {
    if (res == taskStatus.Success) {
        state.common.waitInstallDepend = state.common.waitInstallDepend.filter((depend: string) => {
            return depend != type
        })
        if (state.common.waitInstallDepend.length == 0) {
            state.common.dependInstallState = 'success'
        }
    } else {
        const terminal = useTerminal()
        terminal.toggle(true)
        state.common.dependInstallState = 'fail'
    }
    onRefreshTableData()
}

export const onDisable = (confirmConflict = false) => {
    state.loading.common = true
    state.common.disableHmr = true

    // 拼装依赖处理方案
    if (confirmConflict) {
        const dependConflict: anyObj = {}
        for (const key in state.common.disableDependConflict) {
            if (state.common.disableDependConflict[key]['solution'] != 'delete') {
                continue
            }
            if (typeof dependConflict[state.common.disableDependConflict[key].env] == 'undefined') {
                dependConflict[state.common.disableDependConflict[key].env] = []
            }
            dependConflict[state.common.disableDependConflict[key].env].push(state.common.disableDependConflict[key].depend)
        }
        state.common.disableParams['confirmConflict'] = 1
        state.common.disableParams['dependConflictSolution'] = dependConflict
    }

    changeState(state.common.disableParams)
        .then(() => {
            ElNotification({
                type: 'success',
                message: '操作成功，请清理系统缓存并刷新浏览器~',
            })
            state.dialog.common = false
            onRefreshTableData()
        })
        .catch((res) => {
            if (res.code == -1) {
                state.dialog.common = true
                state.common.dialogTitle = '处理冲突'
                state.common.type = 'disableConfirmConflict'
                state.common.disableDependConflict = res.data.dependConflict
                if (res.data.conflictFile && res.data.conflictFile.length) {
                    const conflictFile = []
                    for (const key in res.data.conflictFile) {
                        conflictFile.push({
                            file: res.data.conflictFile[key],
                        })
                    }
                    state.common.disableConflictFile = conflictFile
                }
            } else if (res.code == -2) {
                state.dialog.common = true
                const commandsData = {
                    type: 'disable',
                    commands: res.data.wait_install,
                }
                state.common.uid = state.goodsInfo.uid
                execCommand(commandsData)
                onRefreshTableData()
            } else if (res.code == -3) {
                // 更新
                onInstall(state.goodsInfo.uid, state.goodsInfo.purchased)
            } else {
                ElNotification({
                    type: 'error',
                    message: res.msg,
                })
            }
        })
        .finally(() => {
            state.loading.common = false
            state.common.disableHmr = true
        })
}

export const onEnable = (uid: string) => {
    state.loading.common = true
    changeState({
        uid: uid,
        state: 1,
    })
        .then(() => {
            state.dialog.common = true
            showCommonLoading('init')
            state.common.dialogTitle = '启用'

            execInstall(uid, 0)
            state.dialog.goodsInfo = false
        })
        .catch((res) => {
            ElNotification({
                type: 'error',
                message: res.msg,
            })
        })
}

export const loginExpired = (res: ApiResponse) => {
    const baAccount = useBaAccount()
    if (res.code == 301 || res.code == 408) {
        baAccount.removeToken()
        state.dialog.baAccount = true
        return true
    }
    return false
}

const modulesOnlyLocalHandle = (modules: anyObj) => {
    if (!state.table.onlyLocal) return modules
    return modules.filter((item: anyObj) => {
        return item.state > moduleInstallState.UNINSTALLED
    })
}

export const execCommand = (data: anyObj) => {
    if (data.type == 'disable') {
        state.dialog.common = true
        state.common.type = 'done'
        state.common.dialogTitle = '等待依赖安装'
        state.common.moduleState = moduleInstallState.DISABLE
        state.common.dependInstallState = 'executing'
        const terminal = useTerminal()
        data.commands.forEach((item: anyObj) => {
            state.common.waitInstallDepend.push(item.type)
            if (item.pm) {
                terminal.addTaskPM(item.command, true, '', (res: number) => {
                    terminalTaskExecComplete(res, item.type)
                })
            } else {
                terminal.addTask(item.command, true, '', (res: number) => {
                    terminalTaskExecComplete(res, item.type)
                })
            }
        })
    }
}

export const currency = (price: number, val: number) => {
    if (typeof price == 'undefined' || typeof val == 'undefined') {
        return '-'
    }
    if (val == 0) {
        return parseInt(price.toString()) + '积分'
    } else {
        return '￥' + price
    }
}

export const moduleStatus = (state: number) => {
    switch (state) {
        case moduleInstallState.INSTALLED:
            return {
                type: '',
                text: '已安装',
            }
        case moduleInstallState.WAIT_INSTALL:
            return {
                type: 'success',
                text: '等待安装',
            }
        case moduleInstallState.CONFLICT_PENDING:
            return {
                type: 'danger',
                text: '冲突待处理',
            }
        case moduleInstallState.DEPENDENT_WAIT_INSTALL:
            return {
                type: 'warning',
                text: '依赖待安装',
            }
        case moduleInstallState.DISABLE:
            return {
                type: 'warning',
                text: '禁用',
            }
        default:
            return {
                type: 'info',
                text: '未知',
            }
    }
}
