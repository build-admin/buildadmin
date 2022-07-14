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

export function checkIn(method: 'get' | 'post', params: object = {}): ApiPromise {
    return createAxios({
        url: controllerUrl + 'checkIn',
        data: params,
        method: method,
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

export function getBalanceLog(page: number, pageSize: number): ApiPromise {
    return createAxios({
        url: accountUrl + 'balance',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    }) as ApiPromise
}

export function getIntegralLog(page: number, pageSize: number): ApiPromise {
    return createAxios({
        url: accountUrl + 'integral',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    }) as ApiPromise
}

export function postLogout(): ApiPromise {
    return createAxios({
        url: controllerUrl + 'logout',
        method: 'POST',
        data: {
            refresh_token: getUserToken('refresh'),
        },
    }) as ApiPromise
}

export function sendRetrievePasswordCode(type: string, account: string): ApiPromise {
    return createAxios(
        {
            url: accountUrl + 'sendRetrievePasswordCode',
            method: 'POST',
            data: {
                type: type,
                account: account,
            },
        },
        {
            showSuccessMessage: true,
        }
    ) as ApiPromise
}

export function sendRegisterCode(params: anyObj): ApiPromise {
    return createAxios(
        {
            url: accountUrl + 'sendRegisterCode',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    ) as ApiPromise
}

export function retrievePassword(params: anyObj): ApiPromise {
    return createAxios(
        {
            url: accountUrl + 'retrievePassword',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    ) as ApiPromise
}
