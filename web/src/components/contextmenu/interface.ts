import type { RouteLocationNormalized } from 'vue-router'

export interface Axis {
    x: number
    y: number
}

export interface ContextMenuItem {
    name: string
    label: string
    icon?: string
    disabled?: boolean
}

export interface ContextmenuItemClickEmitArg extends ContextMenuItem {
    menu?: RouteLocationNormalized
}

export interface Props {
    width?: number
    items: ContextMenuItem[]
}
