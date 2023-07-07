import { state } from './store'
import { index, modules, info, createOrder, payOrder, postInstallModule, getInstallState, changeState, payCheck } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { ElNotification } from 'element-plus'
import { useTerminal } from '/@/stores/terminal'
import { taskStatus } from '/@/components/terminal/constant'
import { moduleInstallState, moduleState } from './types'
import { uuid } from '/@/utils/random'
import { fullUrl } from '/@/utils/common'
import { UserInfo } from '/@/stores/interface'
import { i18n } from '/@/lang/index'

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
        state.sysVersion = res.data.sysVersion
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
    params['sysVersion'] = state.sysVersion
    modules(params)
        .then((res) => {
            if (params.activeTab == 'all') {
                res.data.rows.forEach((item: anyObj) => {
                    moduleUids.push(item.uid)
                })

                state.installedModule.forEach((item) => {
                    if (moduleUids.indexOf(item.uid) === -1) {
                        if (state.table.params.quickSearch) {
                            if (item.title.includes(state.table.params.quickSearch)) res.data.rows.push(item)
                        } else {
                            res.data.rows.push(item)
                        }
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
                        name: i18n.global.t('module.New version'),
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

    info({
        uid: uid,
        localVersion: localItem?.version,
        sysVersion: state.sysVersion,
    })
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
    state.common.dialogTitle = i18n.global.t('module.Install')

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
                        ? i18n.global.t('module.Installation cancelled because module already exists!')
                        : i18n.global.t('module.Installation cancelled because the directory required by the module is occupied!'),
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
            state.common.dialogTitle = i18n.global.t('module.Installation complete')
            state.common.moduleState = moduleInstallState.INSTALLED
            state.common.type = 'done'
        })
        .catch((res) => {
            if (loginExpired(res)) return
            if (res.code == -1) {
                state.common.uid = res.data.uid
                state.common.type = 'InstallConflict'
                state.common.dialogTitle = i18n.global.t('module.A conflict is found Please handle it manually')
                state.common.fileConflict = res.data.fileConflict
                state.common.dependConflict = res.data.dependConflict
            } else if (res.code == -2) {
                state.common.type = 'done'
                state.common.uid = res.data.uid
                state.common.dialogTitle = i18n.global.t('module.Wait for dependent installation')
                state.common.moduleState = moduleInstallState.DEPENDENT_WAIT_INSTALL
                state.common.waitInstallDepend = res.data.wait_install
                state.common.dependInstallState = 'executing'
                const terminal = useTerminal()
                if (res.data.wait_install.includes('npm_dependent_wait_install')) {
                    terminal.addTaskPM('web-install', true, 'module-install:' + res.data.uid, (res: number) => {
                        terminalTaskExecComplete(res, 'npm_dependent_wait_install')
                    })
                }
                if (res.data.wait_install.includes('nuxt_npm_dependent_wait_install')) {
                    terminal.addTaskPM('nuxt-install', true, 'module-install:' + res.data.uid, (res: number) => {
                        terminalTaskExecComplete(res, 'nuxt_npm_dependent_wait_install')
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
                message: i18n.global.t('module.The operation succeeds Please clear the system cache and refresh the browser ~'),
            })
            state.dialog.common = false
            onRefreshTableData()
        })
        .catch((res) => {
            if (res.code == -1) {
                state.dialog.common = true
                state.common.dialogTitle = i18n.global.t('module.Deal with conflict')
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
            state.common.dialogTitle = i18n.global.t('Enable')

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
        state.common.dialogTitle = i18n.global.t('module.Wait for dependent installation')
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

export const specificUserName = (userInfo: Partial<UserInfo>) => {
    return userInfo.nickname + '（' + (userInfo.email || userInfo.mobile || 'ID:' + userInfo.id) + '）'
}

export const currency = (price: number, val: number) => {
    if (typeof price == 'undefined' || typeof val == 'undefined') {
        return '-'
    }
    if (val == 0) {
        return parseInt(price.toString()) + i18n.global.t('module.Points')
    } else {
        return '￥' + price
    }
}

export const moduleStatus = (state: number) => {
    switch (state) {
        case moduleInstallState.INSTALLED:
            return {
                type: '',
                text: i18n.global.t('module.installed'),
            }
        case moduleInstallState.WAIT_INSTALL:
            return {
                type: 'success',
                text: i18n.global.t('module.Wait for installation'),
            }
        case moduleInstallState.CONFLICT_PENDING:
            return {
                type: 'danger',
                text: i18n.global.t('module.Conflict pending'),
            }
        case moduleInstallState.DEPENDENT_WAIT_INSTALL:
            return {
                type: 'warning',
                text: i18n.global.t('module.Dependency to be installed'),
            }
        case moduleInstallState.DISABLE:
            return {
                type: 'warning',
                text: i18n.global.t('Disable'),
            }
        default:
            return {
                type: 'info',
                text: i18n.global.t('Unknown'),
            }
    }
}
