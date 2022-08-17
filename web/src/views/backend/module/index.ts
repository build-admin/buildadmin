import { reactive } from 'vue'
import { modules, info, createOrder, payOrder, postInstallModule, getInstallStateUrl } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { Session } from '/@/utils/storage'
import { INSTALL_MODULE_TEMP } from '/@/stores/constant/cacheKey'
import { ElNotification } from 'element-plus'
import { uuid } from '/@/utils/random'

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
    }
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
    },
})

export const loadData = () => {
    if (typeof state.modules[state.params.activeTab] != 'undefined') {
        return
    }
    state.tableLoading = true
    let params: anyObj = {}
    for (const key in state.params) {
        if (state.params[key] != '') {
            params[key] = state.params[key]
        }
    }
    modules(params)
        .then((res) => {
            state.remark = res.data.remark
            state.modules[params.activeTab] = res.data.rows
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
        .then((res) => {
            state.install.loading = false
        })
        .catch((err) => {
            if (loginExpired(err)) return
            // 冲突
            if (err.code == -1) {
                state.install.loading = false
                state.install.uid = err.data.uid
                state.install.title = '发现冲突，请手动处理'
                state.install.state = err.data.state
                state.install.fileConflict = err.data.fileConflict
                state.install.dependConflict = err.data.dependConflict
            } else if (err.code == -2) {
                state.install.loading = true
                setInstallStateTitle('installDepend')
                state.install.uid = err.data.uid
                state.install.title = '安装依赖'
                state.install.state = err.data.state
            }
        })
        .finally(() => {
            state.publicButtonLoading = false
        })
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
