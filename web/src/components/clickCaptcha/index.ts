import { shortUuid } from '/@/utils/random'
import { VNode, createVNode, render } from 'vue'
import clickCaptchaConstructor from './index.vue'

interface ClickCaptchaOptions {
    // 验证码弹窗的自定义class
    class?: string
    // 前端验证成功时立即清理验证码数据，不可再进行二次验证，不开启则 600s 后自动清理数据
    unset?: boolean
    // 验证失败的提示信息
    error?: string
    // 验证成功的提示信息
    success?: string
}

/**
 * 弹出点击验证码
 * @param uuid 开发者自定义的唯一标识
 * @param callback 验证成功的回调，业务接口可通过 captchaInfo 进行二次验证
 * @param options
 */
const clickCaptcha = (uuid: string, callback?: (captchaInfo: string) => void, options: ClickCaptchaOptions = {}) => {
    let vnode: VNode | null = createVNode(clickCaptchaConstructor, {
        uuid,
        callback,
        ...options,
        key: shortUuid(),
    })
    render(vnode, document.body)
    vnode = null
}

export default clickCaptcha
