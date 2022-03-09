import createAxios from '/@/utils/axios'

export function index() {
    return createAxios({
        url: '/index.php/admin/index/index',
        method: 'get',
    })
}

export function login(method: 'get' | 'post', params: object = {}) {
    return createAxios(
        {
            url: '/index.php/admin/index/login',
            data: params,
            method: method,
        }
    )
}
