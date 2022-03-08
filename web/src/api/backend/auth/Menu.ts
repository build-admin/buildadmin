import createAxios from '/@/utils/axios'

export const indexUrl = '/index.php/admin/auth.menu/index'

export function index() {
    return createAxios({
        url: indexUrl,
        method: 'get',
    })
}
