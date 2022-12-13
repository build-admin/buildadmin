import createAxios from '/@/utils/axios'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useBaAccount } from '/@/stores/baAccount'

const userControllerUrl = '/api/user/'
const captchaUrl = '/api/common/captcha'
const moduleControllerUrl = '/admin/module/'
const storeUrl = '/api/v2.store/'

export function index(params: anyObj = {}) {
    return createAxios({
        url: moduleControllerUrl + 'index',
        method: 'get',
        params: params,
    })
}

export function modules(params: anyObj = {}) {
    const siteConfig = useSiteConfig()
    return createAxios({
        url: siteConfig.apiUrl + storeUrl + 'modules',
        method: 'get',
        params: params,
    })
}

export function info(params: anyObj) {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + storeUrl + 'info',
            method: 'get',
            params: params,
        },
        {
            anotherToken: baAccount.getToken('auth'),
        }
    )
}

export function postLogout(): ApiPromise {
    const siteConfig = useSiteConfig()
    const baAccount = useBaAccount()
    return createAxios({
        url: siteConfig.apiUrl + userControllerUrl + 'logout',
        method: 'POST',
        data: {
            refresh_token: baAccount.getToken('refresh'),
        },
    }) as ApiPromise
}

export function buildCaptchaUrl() {
    const siteConfig = useSiteConfig()
    return siteConfig.apiUrl + captchaUrl + '?server=1'
}

export function checkIn(method: 'get' | 'post', params: object = {}): ApiPromise {
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + userControllerUrl + 'checkIn',
            data: params,
            method: method,
        },
        {
            showSuccessMessage: true,
        }
    ) as ApiPromise
}

export function getUserInfo(): ApiPromise {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + userControllerUrl + 'info',
            method: 'get',
        },
        {
            anotherToken: baAccount.getToken('auth'),
        }
    ) as ApiPromise
}

export function createOrder(params: object = {}): ApiPromise {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + storeUrl + 'order',
            method: 'post',
            params: params,
        },
        {
            anotherToken: baAccount.getToken('auth'),
        }
    ) as ApiPromise
}

export function payOrder(orderId: number, payType: string): ApiPromise {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + storeUrl + 'pay',
            method: 'post',
            params: {
                order_id: orderId,
                pay_type: payType,
            },
        },
        {
            anotherToken: baAccount.getToken('auth'),
            showSuccessMessage: true,
        }
    ) as ApiPromise
}

export function payCheck(sn: string): ApiPromise {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + '/api/pay/check',
            method: 'get',
            params: {
                sn: sn,
            },
        },
        {
            anotherToken: baAccount.getToken('auth'),
            showCodeMessage: false,
        }
    ) as ApiPromise
}

export function getInstallState(uid: string) {
    return createAxios({
        url: moduleControllerUrl + 'state',
        method: 'get',
        params: {
            uid: uid,
        },
    })
}

export function postInstallModule(uid: string, orderId: number, extend: anyObj = {}) {
    const baAccount = useBaAccount()
    return createAxios(
        {
            url: moduleControllerUrl + 'install',
            method: 'post',
            params: {
                uid: uid,
                order_id: orderId,
                token: baAccount.getToken('auth'),
            },
            data: {
                extend: extend,
            },
        },
        {
            showCodeMessage: false,
        }
    ) as ApiPromise
}

export function postUpdate(uid: string, orderId: number, extend: anyObj = {}) {
    const baAccount = useBaAccount()
    return createAxios({
        url: moduleControllerUrl + 'update',
        method: 'POST',
        params: {
            uid,
            order_id: orderId,
            token: baAccount.getToken('auth'),
        },
        data: {
            extend: extend,
        },
    })
}

export function postUninstall(uid: string) {
    return createAxios(
        {
            url: moduleControllerUrl + 'uninstall',
            method: 'post',
            params: {
                uid: uid,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function changeState(params: anyObj) {
    return createAxios(
        {
            url: moduleControllerUrl + 'changeState',
            method: 'post',
            data: params,
        },
        {
            showCodeMessage: false,
        }
    )
}

export function dependentInstallComplete(uid: string) {
    return createAxios({
        url: moduleControllerUrl + 'dependentInstallComplete',
        method: 'post',
        params: {
            uid: uid,
        },
    }) as ApiPromise
}

export function upload(file: string) {
    return createAxios({
        url: moduleControllerUrl + 'upload',
        method: 'post',
        params: {
            file: file,
        },
    }) as ApiPromise
}
