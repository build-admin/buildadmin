import { AxiosPromise } from 'axios'
import createAxios from '/@/utils/axios'

/*
 * 公共请求函数和Url定义
 */

/*
 * URL
 * import { getUrl } from '/@/utils/axios'
 * 不可以直接使用 getUrl() 获取到域名拼接好再返回；会出现`热更新时报 getUrl 未载入`的问题
 */
export const adminUploadUrl = '/index.php/admin/ajax/upload'
export const captchaUrl = '/index.php/api/common/captcha'

/*
 * 远程下拉框数据获取
 */
export function getSelectData(remoteUrl: string, q: string, params: {}) {
    return createAxios({
        url: remoteUrl,
        method: 'get',
        params: Object.assign(params, {
            select: true,
            keyword: q,
        }),
    })
}

/*
 * admin上传文件
 */
export function adminFileUpload(fd: FormData): ApiPromise {
    return createAxios({
        url: adminUploadUrl,
        method: 'POST',
        data: fd,
    }) as ApiPromise
}
