import type { TagProps, ButtonType } from 'element-plus'
declare global {
    interface TableColumn {
        // 渲染为:icon|switch|image|images|tag|url|datetime|buttons
        render?: string
        // 字段 el-table
        prop?: string
        // el-table
        type?: string
        // 字段名 el-table
        label?: string
        // 字段宽度 el-table
        width?: string | number
        // 是否固定 el-table
        fixed?: true | 'left' | 'right'
        // 渲染格式化方法 el-table
        formatter?: Function
        // 对齐方式 el-table
        align?: 'left' | 'center' | 'right'
        // 操作按钮组
        buttons?: OptButton[]
        // 渲染为Tag时:el-tag 组件的主题
        effect?: TagProps['effect']
        // 渲染为Tag时:el-tag 组件的size
        size?: TagProps['size']
        // 自定义数据:比如渲染为Tag时,可以指定不同值时的Tag的Type属性 { open: 'success', close: 'info' }
        custom?: any
        // 渲染为链接时,链接的打开方式
        target?: aTarget
        // 渲染为:a|buttons的点击事件
        click?: Function
        // 最小宽度 el-table
        'min-width'?: string | number
        // 将列中过长的内容 hover 时以 tooltip 的形式显示出来 el-table
        'show-overflow-tooltip'?: boolean
        // header对齐方式 el-table
        'header-align'?: 'left' | 'center' | 'right'
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

    type aTarget = '_blank' | '_self'
}
