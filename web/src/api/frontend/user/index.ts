import createAxios from '/@/utils/axios'
import { useUserInfo } from '/@/stores/userInfo'

export const userUrl = '/api/user/'
export const accountUrl = '/api/account/'

export function checkIn(method: 'get' | 'post', params: object = {}) {
    return createAxios({
        url: userUrl + 'checkIn',
        data: params,
        method: method,
    })
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

export function getProfile() {
    return createAxios({
        url: accountUrl + 'profile',
        method: 'get',
    })
}

export function postVerification(data: anyObj) {
    return createAxios({
        url: accountUrl + 'verification',
        method: 'post',
        data: data,
    })
}

export function postChangeBind(data: anyObj) {
    return createAxios(
        {
            url: accountUrl + 'changeBind',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function changePassword(params: anyObj) {
    return createAxios(
        {
            url: accountUrl + 'changePassword',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function getBalanceLog(page: number, pageSize: number) {
    return createAxios({
        url: accountUrl + 'balance',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    })
}

export function getIntegralLog(page: number, pageSize: number) {
    return createAxios({
        url: accountUrl + 'integral',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    })
}

export function postLogout() {
    const userInfo = useUserInfo()
    return createAxios({
        url: userUrl + 'logout',
        method: 'POST',
        data: {
            refreshToken: userInfo.getToken('refresh'),
        },
    })
}

export function retrievePassword(params: anyObj) {
    return createAxios(
        {
            url: accountUrl + 'retrievePassword',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}
