import { useEventListener } from '@vueuse/core'

/*
 * 显示页面遮罩
 */
export const showShade = function (className = 'shade', closeCallBack: Function): void {
    const containerEl = document.querySelector('.layout-container') as HTMLElement
    const shadeDiv = document.createElement('div')
    shadeDiv.setAttribute('class', 'ba-layout-shade ' + className)
    containerEl.appendChild(shadeDiv)
    useEventListener(shadeDiv, 'click', () => closeShade(closeCallBack))
}

/*
 * 隐藏页面遮罩
 */
export const closeShade = function (closeCallBack: Function = () => {}): void {
    const shadeEl = document.querySelector('.ba-layout-shade') as HTMLElement
    shadeEl && shadeEl.remove()

    closeCallBack()
}
