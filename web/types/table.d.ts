import type { TagProps, ButtonType } from 'element-plus'
declare global {
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
        effect?: TagProps['effect']
        size?: TagProps['size']
        custom?: any
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
        type: ButtonType
        icon: string
    }

    interface TableRow extends anyObj {
        children?: TableRow[]
    }

    type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'recycle bin'
}
