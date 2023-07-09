import createAxios from '/@/utils/axios'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useBaAccount } from '/@/stores/baAccount'

const userControllerUrl = '/api/user/'
const captchaUrl = '/api/common/captcha'
const moduleControllerUrl = '/admin/module/'
const storeUrl = '/api/v6.store/'

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

export function postLogout() {
    const siteConfig = useSiteConfig()
    const baAccount = useBaAccount()
    return createAxios({
        url: siteConfig.apiUrl + userControllerUrl + 'logout',
        method: 'POST',
        data: {
            refreshToken: baAccount.getToken('refresh'),
        },
    })
}

export function buildCaptchaUrl() {
    const siteConfig = useSiteConfig()
    return siteConfig.apiUrl + captchaUrl + '?server=1'
}

export function checkIn(method: 'get' | 'post', params: object = {}) {
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
    )
}

export function getUserInfo() {
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
    )
}

export function createOrder(params: object = {}) {
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
    )
}

export function payOrder(orderId: number, payType: string) {
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
    )
}

export function payCheck(sn: string) {
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
    )
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
                orderId: orderId,
                token: baAccount.getToken('auth'),
            },
            data: {
                extend: extend,
            },
            timeout: 3000 * 10,
        },
        {
            showCodeMessage: false,
        }
    )
}

export function postUpdate(uid: string, orderId: number, extend: anyObj = {}) {
    const baAccount = useBaAccount()
    return createAxios({
        url: moduleControllerUrl + 'update',
        method: 'POST',
        params: {
            uid,
            orderId: orderId,
            token: baAccount.getToken('auth'),
        },
        data: {
            extend: extend,
        },
        timeout: 3000 * 10,
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
    })
}

export function upload(file: string) {
    return createAxios({
        url: moduleControllerUrl + 'upload',
        method: 'post',
        params: {
            file: file,
        },
    })
}
