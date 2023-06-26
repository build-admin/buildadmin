import createAxios from '/@/utils/axios'

export function getAdminRules() {
    return createAxios({
        url: '/admin/auth.Rule/index',
        method: 'get',
    })
}
