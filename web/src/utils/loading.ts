import { nextTick } from 'vue'
import '/@/styles/loading.scss'

export const loading = {
    show: () => {
        const bodys: Element = document.body
        const div = document.createElement('div')
        div.className = 'block-loading'
        div.innerHTML = `
            <div class="block-loading-box">
                <div class="block-loading-box-warp">
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                    <div class="block-loading-box-item"></div>
                </div>
            </div>
        `
        bodys.insertBefore(div, bodys.childNodes[0])
    },
    hide: () => {
        nextTick(() => {
            setTimeout(() => {
                const el = document.querySelector('.block-loading')
                el && el.parentNode?.removeChild(el)
            }, 1000)
        })
    },
}
