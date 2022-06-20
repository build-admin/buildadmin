import createAxios from '/@/utils/axios'
import { getUserToken } from '/@/utils/common'

const controllerUrl = '/index.php/api/user/'

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}

export function checkIn(params: object = {}): ApiPromise {
    return createAxios({
        url: controllerUrl + 'checkIn',
        data: params,
        method: 'POST',
    }) as ApiPromise
}

export function logout() {
    return createAxios({
        url: controllerUrl + 'logout',
        method: 'POST',
        data: {
            refresh_token: getUserToken('refresh'),
        },
    })
}
