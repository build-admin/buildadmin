import createAxios from '/@/utils/axios'
import { getAdminToken } from '/@/utils/common'

const controllerUrl = '/index.php/admin/index/'

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}

export function login(method: 'get' | 'post', params: object = {}): ApiPromise {
    return createAxios({
        url: controllerUrl + 'login',
        data: params,
        method: method,
    }) as ApiPromise
}

export function logout() {
    return createAxios({
        url: controllerUrl + 'logout',
        method: 'POST',
        data: {
            refresh_token: getAdminToken('refresh'),
        },
    })
}
