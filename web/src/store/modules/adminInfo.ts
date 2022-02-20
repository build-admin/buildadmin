import { Module } from 'vuex'
import { AdminInfoStateTypes, RootStateTypes } from '/@/store/interface/index'
import { Local } from '/@/utils/storage'
import { ADMIN_INFO } from '/@/store/constant/cacheKey'

const adminInfo = Local.get(ADMIN_INFO) || {}
const AdminInfoModule: Module<AdminInfoStateTypes, RootStateTypes> = {
    namespaced: true,
    state: {
        adminInfo: adminInfo && adminInfo.token ? adminInfo : {},
    },
    mutations: {
        // 设置管理员资料
        setAndCache(state, data: object): void {
            let adminInfo = Local.get(ADMIN_INFO) || {}
            state.adminInfo = adminInfo = data
            Local.set(ADMIN_INFO, adminInfo)
        },
    },
}

export default AdminInfoModule
