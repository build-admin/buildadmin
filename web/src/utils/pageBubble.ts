// 页面气泡效果

const bubble: {
    width: number
    height: number
    bubbleEl: any
    canvas: any
    ctx: any
    circles: any[]
    animate: boolean
    requestId: any
} = {
    width: 0,
    height: 0,
    bubbleEl: null,
    canvas: null,
    ctx: {},
    circles: [],
    animate: true,
    requestId: null,
}

export const init = function (): void {
    bubble.width = window.innerWidth
    bubble.height = window.innerHeight

    bubble.bubbleEl = document.getElementById('bubble')
    bubble.bubbleEl.style.height = bubble.height + 'px'

    bubble.canvas = document.getElementById('bubble-canvas')
    bubble.canvas.width = bubble.width
    bubble.canvas.height = bubble.height
    bubble.ctx = bubble.canvas.getContext('2d')

    // create particles
    bubble.circles = []
    for (let x = 0; x < bubble.width * 0.5; x++) {
        const c = new Circle()
        bubble.circles.push(c)
    }
    animate()
    addListeners()
}

function scrollCheck() {
    bubble.animate = document.body.scrollTop > bubble.height ? false : true
}

function resize() {
    bubble.width = window.innerWidth
    bubble.height = window.innerHeight
    bubble.bubbleEl.style.height = bubble.height + 'px'
    bubble.canvas.width = bubble.width
    bubble.canvas.height = bubble.height
}

function animate() {
    if (bubble.animate) {
        bubble.ctx.clearRect(0, 0, bubble.width, bubble.height)
        for (const i in bubble.circles) {
            bubble.circles[i].draw()
        }
    }
    bubble.requestId = requestAnimationFrame(animate)
}

class Circle {
    pos: {
        x: number
        y: number
    }
    alpha: number
    scale: number
    velocity: number
    draw: () => void
    constructor() {
        this.pos = {
            x: Math.random() * bubble.width,
            y: bubble.height + Math.random() * 100,
        }
        this.alpha = 0.1 + Math.random() * 0.3
        this.scale = 0.1 + Math.random() * 0.3
        this.velocity = Math.random()
        this.draw = function () {
            this.pos.y -= this.velocity
            this.alpha -= 0.0005
            bubble.ctx.beginPath()
            bubble.ctx.arc(this.pos.x, this.pos.y, this.scale * 10, 0, 2 * Math.PI, false)
            bubble.ctx.fillStyle = 'rgba(255,255,255,' + this.alpha + ')'
            bubble.ctx.fill()
        }
    }
}

function addListeners() {
    window.addEventListener('scroll', scrollCheck)
    window.addEventListener('resize', resize)
}

export function removeListeners() {
    window.removeEventListener('scroll', scrollCheck)
    window.removeEventListener('resize', resize)
    cancelAnimationFrame(bubble.requestId)
}
