import createAxios from '/@/utils/axios'

const controllerUrl = '/index.php/admin/index/'

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}

export function login(method: 'get' | 'post', params: object = {}) {
    return createAxios({
        url: controllerUrl + 'login',
        data: params,
        method: method,
    })
}

export function logout() {
    return createAxios({
        url: controllerUrl + 'logout',
        method: 'POST',
    })
}
