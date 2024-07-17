import type { App } from 'vue'
import { nextTick } from 'vue'
import horizontalScroll from '/@/utils/horizontalScroll'
import { useEventListener } from '@vueuse/core'
import { isString } from 'lodash-es'
import { auth } from '/@/utils/common'

export function directives(app: App) {
    // 鉴权指令
    authDirective(app)
    // 拖动指令
    dragDirective(app)
    // 缩放指令
    zoomDirective(app)
    // 点击后自动失焦指令
    blurDirective(app)
    // 表格横向拖动指令
    tableLateralDragDirective(app)
}

/**
 * 页面按钮鉴权指令
 * @description v-auth="'name'"，name可以为：index,add,edit,del,...
 */
function authDirective(app: App) {
    app.directive('auth', {
        mounted(el, binding) {
            if (!binding.value) return false
            if (!auth(binding.value)) el.parentNode.removeChild(el)
        },
    })
}

/**
 * 表格横向滚动指令
 * @description v-table-lateral-drag
 */
function tableLateralDragDirective(app: App) {
    app.directive('tableLateralDrag', {
        created(el) {
            new horizontalScroll(el.querySelector('.el-table__body-wrapper .el-scrollbar .el-scrollbar__wrap'))
        },
    })
}

/**
 * 点击后自动失焦指令
 * @description v-blur
 */
function blurDirective(app: App) {
    app.directive('blur', {
        mounted(el) {
            useEventListener(el, 'focus', () => el.blur())
        },
    })
}

/**
 * el-dialog 的缩放指令
 * 可以传递字符串和数组
 * 当为字符串时，传递dialog的class即可，实际被缩放的元素为'.el-dialog__body'
 * 当为数组时，参数一为句柄，参数二为实际被缩放的元素
 * @description v-zoom="'.handle-class-name'"
 * @description v-zoom="['.handle-class-name', '.zoom-dom-class-name', 句柄元素高度是否跟随缩放：默认false，句柄元素宽度是否跟随缩放：默认true]"
 */
function zoomDirective(app: App) {
    app.directive('zoom', {
        mounted(el, binding) {
            if (!binding.value) return false
            const zoomDomBindData = isString(binding.value) ? [binding.value, '.el-dialog__body', false, true] : binding.value
            zoomDomBindData[1] = zoomDomBindData[1] ? zoomDomBindData[1] : '.el-dialog__body'
            zoomDomBindData[2] = typeof zoomDomBindData[2] == 'undefined' ? false : zoomDomBindData[2]
            zoomDomBindData[3] = typeof zoomDomBindData[3] == 'undefined' ? true : zoomDomBindData[3]

            nextTick(() => {
                const zoomDom = document.querySelector(zoomDomBindData[1]) as HTMLElement // 实际被缩放的元素
                const zoomDomBox = document.querySelector(zoomDomBindData[0]) as HTMLElement // 动态添加缩放句柄的元素
                const zoomHandleEl = document.createElement('div') // 缩放句柄
                zoomHandleEl.className = 'zoom-handle'
                zoomHandleEl.onmouseenter = () => {
                    zoomHandleEl.onmousedown = (e: MouseEvent) => {
                        const x = e.clientX
                        const y = e.clientY
                        const zoomDomWidth = zoomDom.offsetWidth
                        const zoomDomHeight = zoomDom.offsetHeight
                        const zoomDomBoxWidth = zoomDomBox.offsetWidth
                        const zoomDomBoxHeight = zoomDomBox.offsetHeight
                        document.onmousemove = (e: MouseEvent) => {
                            e.preventDefault() // 移动时禁用默认事件
                            const w = zoomDomWidth + (e.clientX - x) * 2
                            const h = zoomDomHeight + (e.clientY - y)

                            zoomDom.style.width = `${w}px`
                            zoomDom.style.height = `${h}px`

                            if (zoomDomBindData[2]) {
                                const boxH = zoomDomBoxHeight + (e.clientY - y)
                                zoomDomBox.style.height = `${boxH}px`
                            }
                            if (zoomDomBindData[3]) {
                                const boxW = zoomDomBoxWidth + (e.clientX - x) * 2
                                zoomDomBox.style.width = `${boxW}px`
                            }
                        }

                        document.onmouseup = function () {
                            document.onmousemove = null
                            document.onmouseup = null
                        }
                    }
                }

                zoomDomBox.appendChild(zoomHandleEl)
            })
        },
    })
}

/**
 * 拖动指令
 * @description v-drag="[domEl,handleEl]"
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
            const dragHandle = document.querySelector(binding.value[1]) as HTMLElement

            if (!dragHandle || !dragDom) {
                return false
            }

            function down(e: MouseEvent | TouchEvent, type: string): downReturn {
                // 鼠标按下，记录鼠标位置
                const disX = type === 'pc' ? (e as MouseEvent).clientX : (e as TouchEvent).touches[0].clientX
                const disY = type === 'pc' ? (e as MouseEvent).clientY : (e as TouchEvent).touches[0].clientY

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
                styL = +styL.replace(/\px/g, '')
                styT = +styT.replace(/\px/g, '')

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
                const { disX, disY, minDragDomLeft, maxDragDomLeft, minDragDomTop, maxDragDomTop, styL, styT } = obj

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

            dragHandle.onmouseover = () => (dragHandle.style.cursor = `move`)
            dragHandle.onmousedown = (e) => {
                const obj = down(e, 'pc')
                document.onmousemove = (e) => {
                    move(e, 'pc', obj)
                }
                document.onmouseup = () => {
                    document.onmousemove = null
                    document.onmouseup = null
                }
            }
            dragHandle.ontouchstart = (e) => {
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
