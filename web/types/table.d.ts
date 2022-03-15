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
        // 渲染为 datetime 时的格式化方式,字母可以自由组合:y=年,m=月,d=日,h=时,M=分,s=秒
        timeFormat?: 'yyyy-mm-dd hh:MM:ss'
        // 默认值
        default?: any
        // 值替换数据,如{open: '开'}
        replaceValue?: any
        // 自定义渲染方式时,渲染时对值的预处理方法
        renderFormatter?: Function
        // 通用搜索操作符,默认值为=,false禁用此字段通用搜索,支持的操作符见下定义
        operator?: boolean | OperatorStr
        // 通用搜索框的placeholder
        operatorPlaceholder?: string
        // 最小宽度 el-table
        'min-width'?: string | number
        // 将列中过长的内容 hover 时以 tooltip 的形式显示出来 el-table
        'show-overflow-tooltip'?: boolean
        // header对齐方式 el-table
        'header-align'?: 'left' | 'center' | 'right'
    }

    interface OptButton {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮
        render: string
        name: string
        title?: string
        text?: string
        class?: string
        type: ButtonType
        icon: string
        popconfirm?: any
        disabledTip?: boolean
    }

    interface TableRow extends anyObj {
        children?: TableRow[]
    }

    type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'recycle bin' | 'comSearch'

    type OperatorStr =
        | '=' // 等于，默认值
        | '<>'
        | '>'
        | '>='
        | '<'
        | '<='
        | 'LIKE'
        | 'NOT LIKE'
        | 'IN'
        | 'NOT IN'
        | 'RANGE' // 范围，将生成两个输入框 以输入最小值和最大值
        | 'NOT RANGE'
        | 'NULL' // 是否为NULL，将生成单个复选框
        | 'NOT NULL'
        | 'FIND_IN_SET'

    type aTarget = '_blank' | '_self'

    interface comSearchData {
        field: string
        val: string
        operator: string
        render: string
    }
}
