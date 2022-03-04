import type { App } from 'vue'

export function directives(app: App) {
    // 拖动指令
    dragDirective(app)
}

/**
 * 拖动指令
 * @description v-drag="[domEl,handleEl]"`
 * @description domEl=被拖动的元素，handleEl=在此元素内可以拖动`dom`
 */
interface downReturn {
    [key: string]: number
}
function dragDirective(app: App) {
    app.directive('drag', {
        mounted(el, binding) {
            if (!binding.value) return false

            const dragDom = document.querySelector(binding.value[0]) as HTMLElement
            const dragHeader = document.querySelector(binding.value[1]) as HTMLElement

            dragHeader.onmouseover = () => (dragHeader.style.cursor = `move`)

            function down(e: MouseEvent | TouchEvent, type: string): downReturn {
                // 鼠标按下，计算当前元素距离可视区的距离
                const disX =
                    type === 'pc' ? (e as MouseEvent).clientX - dragHeader.offsetLeft : (e as TouchEvent).touches[0].clientX - dragHeader.offsetLeft
                const disY =
                    type === 'pc' ? (e as MouseEvent).clientY - dragHeader.offsetTop : (e as TouchEvent).touches[0].clientY - dragHeader.offsetTop

                // body宽度
                const screenWidth = document.body.clientWidth
                const screenHeight = document.body.clientHeight || document.documentElement.clientHeight

                // 被拖动元素宽度
                const dragDomWidth = dragDom.offsetWidth
                // 被拖动元素高度
                const dragDomheight = dragDom.offsetHeight

                // 拖动限位
                const minDragDomLeft = dragDom.offsetLeft
                const maxDragDomLeft = screenWidth - dragDom.offsetLeft - dragDomWidth
                const minDragDomTop = dragDom.offsetTop
                const maxDragDomTop = screenHeight - dragDom.offsetTop - dragDomheight

                // 获取到的值带px 正则匹配替换
                let styL: string | number = getComputedStyle(dragDom).left
                let styT: string | number = getComputedStyle(dragDom).top

                // 注意在ie中 第一次获取到的值为组件自带50% 移动之后赋值为px
                if (styL.includes('%')) {
                    styL = +document.body.clientWidth * (+styL.replace(/\%/g, '') / 100)
                    styT = +document.body.clientHeight * (+styT.replace(/\%/g, '') / 100)
                } else {
                    styL = +styL.replace(/\px/g, '')
                    styT = +styT.replace(/\px/g, '')
                }

                return {
                    disX,
                    disY,
                    minDragDomLeft,
                    maxDragDomLeft,
                    minDragDomTop,
                    maxDragDomTop,
                    styL,
                    styT,
                }
            }

            function move(e: MouseEvent | TouchEvent, type: string, obj: downReturn) {
                let { disX, disY, minDragDomLeft, maxDragDomLeft, minDragDomTop, maxDragDomTop, styL, styT } = obj

                // 通过事件委托，计算移动的距离
                let left = type === 'pc' ? (e as MouseEvent).clientX - disX : (e as TouchEvent).touches[0].clientX - disX
                let top = type === 'pc' ? (e as MouseEvent).clientY - disY : (e as TouchEvent).touches[0].clientY - disY

                // 边界处理
                if (-left > minDragDomLeft) {
                    left = -minDragDomLeft
                } else if (left > maxDragDomLeft) {
                    left = maxDragDomLeft
                }

                if (-top > minDragDomTop) {
                    top = -minDragDomTop
                } else if (top > maxDragDomTop) {
                    top = maxDragDomTop
                }

                // 移动当前元素
                dragDom.style.cssText += `;left:${left + styL}px;top:${top + styT}px;`
            }

            dragHeader.onmousedown = (e) => {
                const obj = down(e, 'pc')
                document.onmousemove = (e) => {
                    move(e, 'pc', obj)
                }
                document.onmouseup = () => {
                    document.onmousemove = null
                    document.onmouseup = null
                }
            }
            dragHeader.ontouchstart = (e) => {
                const obj = down(e, 'app')
                document.ontouchmove = (e) => {
                    move(e, 'app', obj)
                }
                document.ontouchend = () => {
                    document.ontouchmove = null
                    document.ontouchend = null
                }
            }
        },
    })
}
