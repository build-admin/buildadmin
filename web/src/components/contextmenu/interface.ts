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

export interface ContextMenuItemClickEmitArg<T = any> extends ContextMenuItem {
    sourceData?: T
}

export interface Props {
    width?: number
    items: ContextMenuItem[]
}
