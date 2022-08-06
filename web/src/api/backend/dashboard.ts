import createAxios from '/@/utils/axios'

export function dashboard() {
    return createAxios({
        url: '/admin/Dashboard/dashboard',
        method: 'get',
    })
}
