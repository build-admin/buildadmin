import createAxios from '/@/utils/axios'
export function index() {
    return createAxios({
        url: '/index.php/admin/auth.menu/index',
        method: 'get',
    })
}
