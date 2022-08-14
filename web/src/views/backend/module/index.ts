import { reactive } from 'vue'
import { modules, info, createOrder, payOrder, postInstallTemplate } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'

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
        fileConflict: any[]
        dependConflict: any[]
        uid: string
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
        fileConflict: [],
        dependConflict: [],
        uid: '',
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

export const onInstall = (uid: string, id: number) => {
    state.publicButtonLoading = true
    postInstallTemplate(uid, id)
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
