import axios from 'axios'
import { isProd } from '/@/utils/vite'

var langValue = window.localStorage.getItem('ba-lang') || 'zh-cn'

// 配置新建一个 axios 实例
// baseUrl 在正式环境中，使用当前协议与域名
export const Axios = axios.create({
    baseURL: isProd(process.env.NODE_ENV) ? window.location.protocol + '//' + window.location.host : (import.meta.env.VITE_AXIOS_BASE_URL as string),
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
        return Promise.reject(error)
    }
)

export function errorTips(error: any) {
    if (error.message.indexOf('timeout') != -1) {
        return 'Network Timeout'
    } else if (error.message == 'Network Error') {
        return 'Network connection error'
    } else if (error.response && error.response.status == 404) {
        return 'The interface path cannot be found'
    } else if (error.response && error.response.statusText) {
        return error.response.statusText
    } else {
        return 'unknown error'
    }
}

export interface Response {
    code: number
    msg: string
    time: string
    data: {
        [key: string]: any
    }
}
