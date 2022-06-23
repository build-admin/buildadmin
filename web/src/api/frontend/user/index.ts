import createAxios from '/@/utils/axios'
import { getUserToken } from '/@/utils/common'

const controllerUrl = '/index.php/api/user/'
const accountUrl = '/index.php/api/account/'

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

export function overview() {
    return createAxios({
        url: accountUrl + 'overview',
        method: 'get',
    })
}

export function postProfile(params: anyObj) {
    return createAxios(
        {
            url: accountUrl + 'profile',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function changePassword(params: anyObj): ApiPromise {
    return createAxios(
        {
            url: accountUrl + 'changePassword',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    ) as ApiPromise
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
