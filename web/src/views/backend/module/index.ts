import { reactive } from 'vue'
import { index, modules, info, createOrder, payOrder, postInstallModule, getInstallStateUrl, dependentInstallComplete } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { Session } from '/@/utils/storage'
import { INSTALL_MODULE_TEMP } from '/@/stores/constant/cacheKey'
import { ElNotification } from 'element-plus'
import { uuid } from '/@/utils/random'
import { useTerminal } from '/@/stores/terminal'
import { taskStatus } from '/@/components/terminal/constant'

export enum moduleInstallState {
    UNINSTALLED,
    INSTALLED,
    WAIT_INSTALL,
    CONFLICT_PENDING,
    DEPENDENT_WAIT_INSTALL,
    DIRECTORY_OCCUPIED,
}

export const state: {
    tableLoading: boolean
    remark: string
    modules: anyObj
    category: anyObj
    params: anyObj
    goodsInfo: {
        showDialog: boolean
        info: anyObj
        loading: boolean
    }
    showBaAccount: boolean
    buy: {
        showLoading: boolean
        showDialog: boolean
        info: anyObj
        agreement: boolean
    }
    publicButtonLoading: boolean
    install: {
        showDialog: boolean
        title: string
        loading: boolean
        stateTitle: string
        fileConflict: any[]
        dependConflict: any[]
        uid: string
        state: number
        componentKey: string
        waitInstallDepend: anyObj
        dependInstallState: 'executing' | 'success' | 'fail'
    }
    loadIndex: boolean
    installedModule: {
        uid: string
        state: number
        version: string
        website: string
    }[]
    installedModuleUids: number[]
} = reactive({
    tableLoading: true,
    remark: '',
    modules: [],
    category: [],
    params: {
        quickSearch: '',
        activeTab: 'all',
    },
    goodsInfo: {
        showDialog: false,
        loading: false,
        info: {},
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
    loadIndex: false,
    installedModule: [],
    installedModuleUids: [],
})

export const loadData = () => {
    if (typeof state.modules[state.params.activeTab] != 'undefined') {
        return
    }
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
    let params: anyObj = {}
    for (const key in state.params) {
        if (state.params[key] != '') {
            params[key] = state.params[key]
        }
    }
    modules(params)
        .then((res) => {
            state.remark = res.data.remark
            state.modules[params.activeTab] = res.data.rows.map((item: anyObj) => {
                const idx = state.installedModuleUids.indexOf(item.uid)
                if (idx !== -1) {
                    item.state = state.installedModule[idx].state
                    item.version = state.installedModule[idx].version
                    item.website = state.installedModule[idx].website
                    item.stateTag = moduleState(item.state)
                } else {
                    item.state = 0
                }
                return item
            })
            state.category = res.data.category
        })
        .finally(() => {
            state.tableLoading = false
        })
}

export const showInfo = (id: number) => {
    state.goodsInfo.showDialog = true
    state.goodsInfo.loading = true
    info({ id: id })
        .then((res) => {
            const idx = state.installedModuleUids.indexOf(res.data.info.uid)
            if (idx !== -1) {
                res.data.info.state = state.installedModule[idx].state
                res.data.info.version = state.installedModule[idx].version
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
    state.install.loading = true
    setInstallStateTitle('init')
    state.install.title = '安装'

    // 安装模块可能会触发热更新或热重载造成状态丢失
    // 存储当前模块的安装进度等状态
    Session.set(INSTALL_MODULE_TEMP, { uid: uid, id: id })

    // 获取安装状态
    getInstallStateUrl(uid).then((res) => {
        state.install.state = res.data.state
        if (state.install.state === moduleInstallState.INSTALLED || state.install.state === moduleInstallState.DIRECTORY_OCCUPIED) {
            ElNotification({
                type: 'error',
                message: state.install.state === moduleInstallState.INSTALLED ? '安装取消，因为模块已经存在！' : '安装取消，因为模块所需目录被占用！',
            })
            state.install.showDialog = false
            state.install.loading = false
        } else {
            setInstallStateTitle(state.install.state === moduleInstallState.UNINSTALLED ? 'download' : 'install')
            execInstall(uid, id)
            // 关闭其他弹窗
            state.goodsInfo.showDialog = false
            state.buy.showDialog = false
            state.showBaAccount = false
        }
    })
}

const setInstallStateTitle = (installState: string) => {
    state.install.stateTitle = installState
    state.install.componentKey = uuid()
}

export const execInstall = (uid: string, id: number, extend: anyObj = {}) => {
    postInstallModule(uid, id, extend)
        .then(() => {
            state.install.uid = uid
            state.install.title = '安装完成'
            state.install.state = moduleInstallState.INSTALLED
            Session.remove(INSTALL_MODULE_TEMP)
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
                Session.remove(INSTALL_MODULE_TEMP)

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
            }
        })
        .finally(() => {
            state.install.loading = false
            state.publicButtonLoading = false
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

export const moduleState = (state: number) => {
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
        default:
            return {
                type: 'info',
                text: '未知',
            }
    }
}
