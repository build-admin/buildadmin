import createAxios from '/@/utils/axios'
import { getAdminToken, isAdminApp } from '/@/utils/common'
import { getUrl } from '/@/utils/axios'

/*
 * 公共请求函数和Url定义
 */

// Admin模块
export const adminUploadUrl = '/index.php/admin/ajax/upload'
export const adminBuildSuffixSvgUrl = '/index.php/admin/ajax/buildSuffixSvg'
export const adminAreaUrl = '/index.php/admin/ajax/area'
export const getTablePkUrl = '/index.php/admin/ajax/getTablePk'
export const terminalUrl = '/index.php/admin/install/terminal'
export const changeTerminalConfigUrl = '/index.php/admin/ajax/changeTerminalConfig'

// 公共
export const captchaUrl = '/index.php/api/common/captcha'
export const refreshTokenUrl = '/index.php/api/common/refreshToken'

// api模块(前台)
export const apiUploadUrl = '/index.php/api/ajax/upload'
export const apiBuildSuffixSvgUrl = '/index.php/api/ajax/buildSuffixSvg'
export const apiAreaUrl = '/index.php/api/ajax/area'

/**
 * 上传文件
 */
export function fileUpload(fd: FormData): ApiPromise {
    return createAxios({
        url: isAdminApp() ? adminUploadUrl : apiUploadUrl,
        method: 'POST',
        data: fd,
    }) as ApiPromise
}

/**
 * 生成文件后缀icon的svg图片
 * @param suffix 后缀名
 * @param background 背景色,如:rgb(255,255,255)
 */
export function buildSuffixSvgUrl(suffix: string, background: string = '') {
    return (
        getUrl() +
        (isAdminApp() ? adminBuildSuffixSvgUrl : apiBuildSuffixSvgUrl) +
        '?batoken=' +
        getAdminToken() +
        '&suffix=' +
        suffix +
        (background ? '&background=' + background : '')
    )
}

/**
 * 获取地区数据
 */
export function getArea(values: number[]) {
    let params: { province?: number; city?: number } = {}
    if (values[0]) {
        params.province = values[0]
    }
    if (values[1]) {
        params.city = values[1]
    }
    return createAxios({
        url: isAdminApp() ? adminAreaUrl : apiAreaUrl,
        method: 'GET',
        params: params,
    })
}

/**
 * 构建命令执行窗口url
 */
export function buildTerminalUrl(commandKey: string, outputExtend: string) {
    return getUrl() + terminalUrl + '?command=' + commandKey + '&extend=' + outputExtend + '&batoken=' + getAdminToken()
}

/**
 * 请求修改终端配置
 */
export function postChangeTerminalConfig(data: { manager?: string; port?: string }): ApiPromise {
    return createAxios(
        {
            url: changeTerminalConfigUrl,
            method: 'POST',
            data: data,
        },
        {
            loading: true,
        }
    ) as ApiPromise
}

/**
 * 远程下拉框数据获取
 */
export function getSelectData(remoteUrl: string, q: string, params: {}) {
    return createAxios({
        url: remoteUrl,
        method: 'get',
        params: Object.assign(params, {
            select: true,
            quick_search: q,
        }),
    })
}

export function buildCaptchaUrl() {
    return getUrl() + captchaUrl
}

export function getTablePk(table: string) {
    return createAxios({
        url: getTablePkUrl,
        method: 'get',
        params: {
            table: table,
        },
    })
}

export function refreshToken(): ApiPromise {
    return createAxios({
        url: refreshTokenUrl,
        method: 'POST',
        data: {
            refresh_token: getAdminToken('refresh'),
        },
    }) as ApiPromise
}

/**
 * 生成一个控制器的：增、删、改、查、排序的操作url
 */
export class baTableApi {
    private controllerUrl
    public actionUrl

    constructor(controllerUrl: string) {
        this.controllerUrl = controllerUrl
        this.actionUrl = new Map([
            ['index', controllerUrl + 'index'],
            ['add', controllerUrl + 'add'],
            ['edit', controllerUrl + 'edit'],
            ['del', controllerUrl + 'del'],
            ['sortable', controllerUrl + 'sortable'],
        ])
    }

    index(filter: anyObj = {}): ApiPromise<TableDefaultData> {
        return createAxios({
            url: this.actionUrl.get('index'),
            method: 'get',
            params: filter,
        }) as ApiPromise
    }

    edit(params: anyObj) {
        return createAxios({
            url: this.actionUrl.get('edit'),
            method: 'get',
            params: params,
        })
    }

    del(ids: string[]) {
        return createAxios(
            {
                url: this.actionUrl.get('del'),
                method: 'DELETE',
                data: {
                    ids: ids,
                },
            },
            {
                showSuccessMessage: true,
            }
        )
    }

    postData(action: string, data: anyObj) {
        if (!this.actionUrl.has(action)) {
            throw new Error('action 不存在！')
        }
        return createAxios(
            {
                url: this.actionUrl.get(action),
                method: 'post',
                data: data,
            },
            {
                showSuccessMessage: true,
            }
        )
    }

    sortableApi(id: number, targetId: number) {
        return createAxios({
            url: this.actionUrl.get('sortable'),
            method: 'post',
            data: {
                id: id,
                targetId: targetId,
            },
        })
    }
}
