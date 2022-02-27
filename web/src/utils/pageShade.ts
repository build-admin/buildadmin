// 手机端显示菜单时同时显示阴影遮罩
export const showShade = function (className = 'shade', closeCallBack: Function): void {
    const containerEl = document.querySelector('.layout-container') as HTMLElement
    const shadeDiv = document.createElement('div')
    shadeDiv.setAttribute('class', 'ba-layout-shade ' + className)
    containerEl.appendChild(shadeDiv)
    shadeDiv.addEventListener('click', () => {
        closeShade(closeCallBack)
    })
}

export const closeShade = function (closeCallBack: Function = () => {}): void {
    const shadeEl = document.querySelector('.ba-layout-shade') as HTMLElement
    shadeEl && shadeEl.remove()

    closeCallBack()
}
