import createAxios from '/@/utils/axios'

export function dashboard() {
    return createAxios({
        url: '/index.php/admin/Dashboard/dashboard',
        method: 'get',
    })
}
