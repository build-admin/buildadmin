/**
 * 横向滚动条
 */
export default class horizontalScroll {
    private el: HTMLElement

    constructor(nativeElement: HTMLElement) {
        this.el = nativeElement
        this.handleWheelEvent()
    }

    handleWheelEvent() {
        let wheel = ''

        if ('onmousewheel' in this.el) {
            wheel = 'mousewheel'
        } else if ('onwheel' in this.el) {
            wheel = 'wheel'
        } else if ('attachEvent' in window) {
            wheel = 'onmousewheel'
        } else {
            wheel = 'DOMMouseScroll'
        }
        this.el['addEventListener'](wheel, this.scroll, { passive: true })
    }

    scroll = (event: any) => {
        if (this.el.clientWidth >= this.el.scrollWidth) {
            return
        }
        this.el.scrollLeft += event.deltaY ? event.deltaY : event.detail && event.detail !== 0 ? event.detail : -event.wheelDelta
    }
}
