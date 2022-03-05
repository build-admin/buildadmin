interface TableColumn {
    render?: string
    prop?: string
    type?: string
    label?: string
    width?: string | number
    fixed?: true | 'left' | 'right'
    formatter?: Function
    align?: 'left' | 'center' | 'right'
    buttons?: OptButton[]
    'min-width'?: string | number
    'show-overflow-tooltip'?: boolean
    'header-align'?: 'left' | 'center' | 'right'
    'class-name'?: string
    'label-class-name'?: string
}

interface OptButton {
    name: string
    title?: string
    text?: string
    class?: string
    click: Function
    type: '' | 'default' | 'info' | 'success' | 'warning' | 'text' | 'primary' | 'danger'
    icon: string
}

interface TableRow {
    [key: string]: any
    children?: TableRow[]
}

type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'recycle bin'
