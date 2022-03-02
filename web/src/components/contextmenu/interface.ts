import { viewMenu } from '/@/stores/interface'

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
    menu?: viewMenu
}

/*
 * defineProps 中不能使用导出的 interface
 * vue的issue已存在，尚未解决
 * 此处预定义上，解决此问题后再使用
 */
export interface Props {
    width?: number
    items: ContextMenuItem[]
}
