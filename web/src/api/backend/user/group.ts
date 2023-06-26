import createAxios from '/@/utils/axios'

export function getUserRules() {
    return createAxios({
        url: '/admin/user.Rule/index',
        method: 'get',
    })
}
