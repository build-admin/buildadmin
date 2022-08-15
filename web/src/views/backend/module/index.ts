import { reactive } from 'vue'
import { modules, info, createOrder, payOrder, postInstallModule, getInstallStateUrl } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { Session } from '/@/utils/storage'
import { INSTALL_MODULE_TEMP } from '/@/stores/constant/cacheKey'
import { ElNotification } from 'element-plus'
import { uuid } from '/@/utils/random'

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

    // 安装模块可能会触发热更新或热重载造成状态丢失
    // 存储当前模块的安装进度等状态
    Session.set(INSTALL_MODULE_TEMP, { uid: uid, id: id })

    // 获取安装状态
    getInstallStateUrl(uid).then((res) => {
        state.install.state = res.data.state
        if (state.install.state === 1 || state.install.state === 4) {
            ElNotification({
                type: 'error',
                message: state.install.state === 1 ? '安装取消，因为模块已经存在！' : '安装取消，因为模块所需目录被占用！',
            })
            state.install.showDialog = false
            state.install.loading = false
        } else {
            setInstallStateTitle(state.install.state === 0 ? 'download' : 'install')
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

const execInstall = (uid: string, id: number) => {
    console.log('请求安装')
    return false
    postInstallModule(uid, id)
        .then((res) => {
            // 安装成功
            // 是否增加了依赖？
            // 是否需要npm build
        })
        .catch((err) => {
            if (loginExpired(err)) return
            if (err.code == -1) {
                state.install.showDialog = true
                state.install.uid = err.data.uid
                state.install.title = err.data.title
                state.install.fileConflict = err.data.fileConflict
                state.install.dependConflict = err.data.dependConflict
            }
        })
        .finally(() => {
            state.install.loading = false
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
