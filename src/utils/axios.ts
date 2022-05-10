import axios from 'axios'
import { isProd } from '/@/utils/vite'
import { ElNotification } from 'element-plus'
import { i18n } from '../lang'

var langValue = window.localStorage.getItem('ba-lang') || 'zh-cn'

/**
 * 获取baseUrl
 */
export const getUrl = () => {
    return isProd(process.env.NODE_ENV) ? window.location.protocol + '//' + window.location.host : (import.meta.env.VITE_AXIOS_BASE_URL as string)
}

// 配置新建一个 axios 实例
// baseUrl 在正式环境中，使用当前协议与域名
export const Axios = axios.create({
    baseURL: getUrl(),
    timeout: 50000,
    headers: {
        'Content-Type': 'application/json',
        'think-lang': langValue,
    },
})

Axios.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        httpErrorStatusHandle(error)
        return Promise.reject(error)
    }
)

export function httpErrorStatusHandle(error: any) {
    let message = ''
    if (error && error.response) {
        switch (error.response.status) {
            case 404:
                message = i18n.global.t('The interface path cannot be found', { url: error.response.config.url })
                break
            case 408:
                message = i18n.global.t('Request timeout!')
                break
            case 500:
                message = i18n.global.t('Server internal error!')
                break
            case 504:
                message = i18n.global.t('The service is temporarily unavailable. Please try again later!')
                break
            default:
                message = i18n.global.t('Abnormal problem, please contact the website administrator!')
                break
        }
    }
    if (error.message.includes('timeout')) message = i18n.global.t('Network Timeout')
    if (error.message.includes('Network Error')) message = i18n.global.t('Network connection error')
    if (error.message.includes('Network'))
        message = window.navigator.onLine
            ? i18n.global.t('Abnormal problem, please contact the website administrator!')
            : i18n.global.t("You're disconnected!")

    if (!message) {
        message = i18n.global.t('unknown error')
    }

    ElNotification({
        type: 'error',
        message,
    })
}
