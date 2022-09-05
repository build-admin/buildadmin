import { reactive } from 'vue'
import { index, modules, info, createOrder, payOrder, postInstallModule, getInstallStateUrl, changeState } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { Session } from '/@/utils/storage'
import { ElNotification } from 'element-plus'
import { uuid } from '/@/utils/random'
import { useTerminal } from '/@/stores/terminal'
import { taskStatus } from '/@/components/terminal/constant'
import { moduleInstallState, moduleState } from './types'

export const INSTALL_MODULE_TEMP = 'installModuleTemp' // 模块安装状态临时记录
export const VITE_FULL_RELOAD = 'viteFullReload' // 是否触发了vite热重载的临时记录
export const DEPEND_DATA_TEMP = 'dependDataTemp' // 依赖更新临时记录

export const state: moduleState = reactive({
    tableLoading: true,
    remark: '',
    modules: [],
    modulesEbak: [],
    category: [],
    params: {
        quickSearch: '',
        activeTab: 'all',
    },
    goodsInfo: {
        showDialog: false,
        loading: false,
        info: {},
        showConfirmConflict: false,
        conflictFile: [],
        dependConflict: false,
    },
    showBaAccount: false,
    buy: {
        showLoading: false,
        showDialog: false,
        info: {},
        agreement: true,
    },
    publicButtonLoading: false,
    install: {
        showDialog: false,
        title: '',
        loading: false,
        stateTitle: 'init',
        fileConflict: [],
        dependConflict: [],
        uid: '',
        state: 0,
        componentKey: uuid(),
        waitInstallDepend: {},
        dependInstallState: 'executing',
    },
    onlyLocal: false,
    loadIndex: false,
    installedModule: [],
    installedModuleUids: [],
    waitFullReload: false,
    moduleDisableParams: {},
})

export const loadData = () => {
    state.tableLoading = true
    if (!state.loadIndex) {
        loadIndex().then(() => {
            getModules()
        })
    } else {
        getModules()
    }
}

const loadIndex = () => {
    return index().then((res) => {
        state.loadIndex = true
        state.installedModule = res.data.installedModule
        let installedModuleUids = []
        if (res.data.installedModule) {
            for (const key in res.data.installedModule) {
                installedModuleUids.push(res.data.installedModule[key].uid)
            }
            state.installedModuleUids = installedModuleUids
        }
    })
}

const getModules = () => {
    if (typeof state.modulesEbak[state.params.activeTab] != 'undefined') {
        state.modules[state.params.activeTab] = modulesOnlyLocalHandle(state.modulesEbak[state.params.activeTab])
        state.tableLoading = false
        return
    }
    let params: anyObj = {}
    for (const key in state.params) {
        if (state.params[key] != '') {
            params[key] = state.params[key]
        }
    }
    let installedModule: { uid: string; version: string }[] = []
    state.installedModule.forEach((item) => {
        installedModule.push({
            uid: item.uid,
            version: item.version,
        })
    })
    params['installed'] = installedModule
    modules(params)
        .then((res) => {
            state.remark = res.data.remark
            state.modulesEbak[params.activeTab] = res.data.rows.map((item: anyObj) => {
                const idx = state.installedModuleUids.indexOf(item.uid)
                if (idx !== -1) {
                    item.state = state.installedModule[idx].state
                    item.version = state.installedModule[idx].version
                    item.website = state.installedModule[idx].website
                    item.stateTag = moduleStatus(item.state)
                } else {
                    item.state = 0
                }

                if (item.new_version) {
                    item.tags.push({
                        name: '有新版本',
                        type: 'danger',
                    })
                }

                return item
            })
            state.modules[params.activeTab] = modulesOnlyLocalHandle(state.modulesEbak[params.activeTab])
            state.category = res.data.category
        })
        .finally(() => {
            state.tableLoading = false
        })
}

const modulesOnlyLocalHandle = (modules: anyObj) => {
    if (!state.onlyLocal) return modules
    return modules.filter((item: anyObj) => {
        return item.state > moduleInstallState.UNINSTALLED
    })
}

export const onRefreshData = () => {
    state.loadIndex = false
    for (const key in state.modulesEbak) {
        state.modulesEbak[key] = undefined
    }
    loadData()
}

export const showInfo = (uid: string) => {
    state.goodsInfo.showDialog = true
    state.goodsInfo.loading = true

    const localItem = state.installedModule.find((item) => {
        return item.uid == uid
    })

    info({ uid: uid, localVersion: localItem?.version })
        .then((res) => {
            const idx = state.installedModuleUids.indexOf(res.data.info.uid)
            if (idx !== -1) {
                res.data.info.state = state.installedModule[idx].state
                res.data.info.version = state.installedModule[idx].version
                res.data.info.enable = res.data.info.state === moduleInstallState.DISABLE ? false : true
            } else {
                res.data.info.state = 0
            }
            state.goodsInfo.info = res.data.info
        })
        .catch((err) => {
            if (loginExpired(err)) {
                state.goodsInfo.showDialog = false
            }
        })
        .finally(() => {
            state.goodsInfo.loading = false
        })
}

export const onBuy = () => {
    state.buy.showDialog = true
    state.buy.showLoading = true
    createOrder({
        goods_id: state.goodsInfo.info.id,
    })
        .then((res) => {
            state.buy.showLoading = false
            state.buy.info = res.data.info
        })
        .catch((err) => {
            state.buy.showDialog = false
            state.buy.showLoading = false
            loginExpired(err)
        })
}

export const onPay = (payType: number) => {
    state.publicButtonLoading = true
    payOrder(state.buy.info.id, payType)
        .then((res) => {
            onInstall(res.data.info.uid, res.data.info.id)
        })
        .catch((err) => {
            loginExpired(err)
        })
        .finally(() => {
            state.publicButtonLoading = false
        })
}

export const onInstall = (uid: string, id: number) => {
    state.install.showDialog = true
    setInstallLoadingStateTitle('init')
    state.install.title = '安装'

    // 安装模块可能会触发热更新或热重载造成状态丢失
    // 存储当前模块的安装进度等状态
    Session.set(INSTALL_MODULE_TEMP, { uid: uid, id: id })

    // 是否发生了热重载
    const viteFullReload = Session.get(VITE_FULL_RELOAD)

    // 获取安装状态
    getInstallStateUrl(uid).then((res) => {
        state.install.state = res.data.state

        if (state.install.state === moduleInstallState.INSTALLED && viteFullReload) {
            state.install.title = '安装完成'
            setInstallLoadingStateTitle(false)
            clearTempStorage()
            return
        }

        if (
            state.install.state === moduleInstallState.INSTALLED ||
            state.install.state === moduleInstallState.DISABLE ||
            state.install.state === moduleInstallState.DIRECTORY_OCCUPIED
        ) {
            ElNotification({
                type: 'error',
                message:
                    state.install.state === moduleInstallState.INSTALLED || state.install.state === moduleInstallState.DISABLE
                        ? '安装取消，因为模块已经存在！'
                        : '安装取消，因为模块所需目录被占用！',
            })
            state.install.showDialog = false
            setInstallLoadingStateTitle(false)
            clearTempStorage()
        } else {
            setInstallLoadingStateTitle(state.install.state === moduleInstallState.UNINSTALLED ? 'download' : 'install')
            execInstall(uid, id)

            // 关闭其他弹窗
            state.goodsInfo.showDialog = false
            state.buy.showDialog = false
            state.showBaAccount = false
        }
    })
}

export const execInstall = (uid: string, id: number, extend: anyObj = {}) => {
    const viteFullReload = Session.get(VITE_FULL_RELOAD)

    postInstallModule(uid, id, extend)
        .then((res) => {
            state.install.title = '安装完成'
            state.install.state = moduleInstallState.INSTALLED
            if (parseInt(res.data.data.fullreload) === 0 || viteFullReload) {
                clearTempStorage()
            } else {
                state.waitFullReload = true
            }
        })
        .catch((err) => {
            if (loginExpired(err)) return
            // 冲突
            if (err.code == -1) {
                state.install.uid = err.data.uid
                state.install.title = '发现冲突，请手动处理'
                state.install.state = err.data.state
                state.install.fileConflict = err.data.fileConflict
                state.install.dependConflict = err.data.dependConflict
            } else if (err.code == -2) {
                state.install.uid = err.data.uid
                state.install.title = '依赖待安装'
                state.install.state = moduleInstallState.DEPENDENT_WAIT_INSTALL
                state.install.waitInstallDepend = err.data.wait_install
                state.install.dependInstallState = 'executing'
                if (extend.type && extend.type == 'conflictHandle' && parseInt(err.data.fullreload) === 1) {
                    state.waitFullReload = true
                    return
                }
                if (parseInt(err.data.fullreload) === 0 || viteFullReload) {
                    const terminal = useTerminal()
                    if (err.data.wait_install.includes('npm_dependent_wait_install')) {
                        terminal.addTaskPM('web-install', true, 'module-install:' + err.data.uid, (res: number) => {
                            terminalTaskExecComplete(res, 'npm_dependent_wait_install')
                        })
                    }
                    if (err.data.wait_install.includes('composer_dependent_wait_install')) {
                        terminal.addTask('composer.update', true, 'module-install:' + err.data.uid, (res: number) => {
                            terminalTaskExecComplete(res, 'composer_dependent_wait_install')
                        })
                    }
                    clearTempStorage()
                } else {
                    state.waitFullReload = true
                }
            } else if (err.code == 0) {
                ElNotification({
                    type: 'error',
                    message: err.msg,
                })
                state.install.showDialog = false
                clearTempStorage()
            }
        })
        .finally(() => {
            setInstallLoadingStateTitle(false)
            state.publicButtonLoading = false
            onRefreshData()
        })
}

const terminalTaskExecComplete = (res: number, type: string) => {
    if (res == taskStatus.Success) {
        state.install.waitInstallDepend = state.install.waitInstallDepend.filter((depend: string) => {
            return depend != type
        })
        if (state.install.waitInstallDepend.length == 0) {
            state.install.dependInstallState = 'success'
        }
    } else {
        const terminal = useTerminal()
        terminal.toggle(true)
        state.install.dependInstallState = 'fail'
    }
    onRefreshData()
}

const setInstallLoadingStateTitle = (installState: string | false) => {
    if (installState === false) {
        state.install.loading = false
        return
    }
    state.install.loading = true
    state.install.stateTitle = installState
    state.install.componentKey = uuid()
}

const clearTempStorage = () => {
    Session.remove(INSTALL_MODULE_TEMP)
    Session.remove(VITE_FULL_RELOAD)
}

export const postDisable = (confirmConflict: boolean = false) => {
    state.publicButtonLoading = true
    state.moduleDisableParams['confirmConflict'] = confirmConflict
    changeState(state.moduleDisableParams)
        .then((res) => {
            ElNotification({
                type: 'success',
                message: '操作成功，请清理系统缓存并刷新浏览器~',
            })
            state.goodsInfo.showConfirmConflict = false
            onRefreshData()
        })
        .catch((res) => {
            state.goodsInfo.info.enable = !state.goodsInfo.info.enable
            if (res.code == -1) {
                state.goodsInfo.showConfirmConflict = true
                state.goodsInfo.dependConflict = res.data.dependConflict
                if (res.data.conflictFile && res.data.conflictFile.length) {
                    let conflictFile = []
                    for (const key in res.data.conflictFile) {
                        conflictFile.push({
                            file: res.data.conflictFile[key],
                        })
                    }
                    state.goodsInfo.conflictFile = conflictFile
                }
            } else if (res.code == -2) {
                state.goodsInfo.showConfirmConflict = false
                if (parseInt(res.data.fullreload) === 1) {
                    Session.set(DEPEND_DATA_TEMP, res.data.wait_install)
                    state.install.showDialog = true
                    state.install.title = '请稍等'
                    state.waitFullReload = true
                } else {
                    execCommand(res.data.wait_install)
                    onRefreshData()
                }
            } else if (res.code == -3) {
                // 更新
                if (parseInt(res.data.fullreload) === 1) {
                    state.install.showDialog = true
                    state.install.title = '请稍等'
                    state.waitFullReload = true
                    Session.set(INSTALL_MODULE_TEMP, { uid: state.goodsInfo.info.uid, id: state.goodsInfo.info.purchased })
                } else {
                    onInstall(state.goodsInfo.info.uid, state.goodsInfo.info.purchased)
                }
            } else {
                ElNotification({
                    type: 'error',
                    message: res.msg,
                })
            }
        })
        .finally(() => {
            state.publicButtonLoading = false
        })
}

export const onEnable = (uid: string) => {
    state.publicButtonLoading = true
    changeState({
        uid: uid,
        state: 1,
    })
        .then(() => {
            state.install.showDialog = true
            setInstallLoadingStateTitle('init')
            state.install.title = '启用'
            // 安装模块可能会触发热更新或热重载造成状态丢失
            // 存储当前模块的安装进度等状态
            Session.set(INSTALL_MODULE_TEMP, { uid: uid })

            execInstall(uid, 0)

            // 关闭其他弹窗
            state.goodsInfo.showDialog = false
        })
        .catch((res) => {
            ElNotification({
                type: 'error',
                message: res.msg,
            })
        })
}

export const execCommand = (data: anyObj, extend: string = '') => {
    if (Array.isArray(data) && data.length) {
        const terminal = useTerminal()
        terminal.toggle(true)
        data.forEach((item) => {
            if (item.pm) {
                terminal.addTaskPM(item.command, true, '', (res: number) => {
                    if (res == taskStatus.Failed) terminal.toggle(true)
                })
            } else {
                terminal.addTask(item.command, true, '', (res: number) => {
                    if (res == taskStatus.Failed) terminal.toggle(true)
                })
            }
        })
    }
    Session.remove(VITE_FULL_RELOAD)
    Session.remove(DEPEND_DATA_TEMP)
}

export const loginExpired = (res: ApiResponse) => {
    const baAccount = useBaAccount()
    if (res.code == 301 || res.code == 408) {
        baAccount.removeToken()
        state.showBaAccount = true
        return true
    }
    return false
}

export const currency = (price: number, val: number) => {
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
