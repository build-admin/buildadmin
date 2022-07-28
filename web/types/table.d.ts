import type { TagProps, ButtonType, ElForm } from 'element-plus'
import Table from '/@/components/table/index.vue'
declare global {
    /* baTable */
    interface BaTable {
        ref?: typeof Table
        pk?: string
        data?: TableRow[]
        remark?: string | null
        loading?: boolean
        expandAll?: boolean
        selection?: TableRow[]
        dblClickNotEditColumn?: (string | undefined)[]
        column: TableColumn[]
        total?: number
        filter?: anyObj
        defaultOrder?: { prop: string; order: string }
        dragSortLimitField?: string
        acceptQuery?: boolean
        showComSearch?: boolean
        extend?: anyObj
    }

    /* baTableForm */
    interface BaTableForm {
        ref?: InstanceType<typeof ElForm> | undefined
        labelWidth?: number
        operate?: string
        operateIds?: string[]
        items?: anyObj
        submitLoading?: boolean
        defaultItems?: anyObj
        loading?: boolean
        extend?: anyObj
    }

    /* BaTable前置处理函数(前置埋点) */
    interface BaTableBefore {
        getIndex?: Function
        postDel?: Function
        requestEdit?: Function
        onTableDblclick?: Function
        toggleForm?: Function
        onSubmit?: Function
        onTableAction?: Function
        onTableHeaderAction?: Function
        mount?: Function
        [key: string]: Function | undefined
    }

    /* BaTable后置处理函数(后置埋点) */
    interface BaTableAfter {
        getIndex?: Function
        postDel?: Function
        requestEdit?: Function
        onTableDblclick?: Function
        toggleForm?: Function
        onSubmit?: Function
        onTableAction?: Function
        onTableHeaderAction?: Function
        mount?: Function
        [key: string]: Function | undefined
    }

    interface ComSearch {
        form: anyObj
        fieldData: Map<string, any>
    }

    /* 表格列 */
    interface TableColumn extends ElTableColumn {
        // 是否显示
        show?: boolean
        // 渲染为:icon|switch|image|images|tag|url|datetime|buttons|customTemplate
        render?: string
        // 操作按钮组
        buttons?: OptButton[]
        // 渲染为Tag时:el-tag 组件的主题
        effect?: TagProps['effect']
        // 渲染为Tag时:el-tag 组件的size
        size?: TagProps['size']
        // 自定义数据:比如渲染为Tag时,可以指定不同值时的Tag的Type属性 { open: 'success', close: 'info' }
        custom?: any
        // 谨慎使用：自定义渲染模板，方法可返回html内容，请确保返回内容是xss安全的
        customTemplate?: (row: TableRow, field: TableColumn, value: any) => string
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
        // 使用了 render 属性时,渲染前对字段值的预处理方法，请返回新值
        renderFormatter?: (row: TableRow, field: TableColumn, value: any) => any
        // 通用搜索操作符,默认值为=,false禁用此字段通用搜索,支持的操作符见下定义
        operator?: boolean | OperatorStr
        // 通用搜索框的placeholder
        operatorPlaceholder?: string
        // 公共搜索渲染方式:上方的 render=tag|switch 时公共搜索也会渲染为下拉，数字会渲染为范围筛选，时间渲染为时间选择器等
        comSearchRender?: 'remoteSelect' | 'select'
        // 远程属性
        remote?: {
            pk?: string
            field?: string
            params?: anyObj
            multiple?: boolean
            remoteUrl: string
        }
    }

    /* 表格右侧操作按钮 */
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
        // 按钮是否显示，请返回布尔值
        display?: (row: TableRow, field: TableColumn) => boolean
    }

    /* 表格行 */
    interface TableRow extends anyObj {
        children?: TableRow[]
    }

    /* 表头支持的按钮 */
    type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'recycle bin' | 'comSearch'

    /* 通用搜索操作符支持的值 */
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

    /* 链接打开方式 */
    type aTarget = '_blank' | '_self'

    /* 通用搜索事件返回的Data */
    interface comSearchData {
        field: string
        val: string
        operator: string
        render: string
    }

    interface ElTreeData {
        label: string
        children?: ElTreeData[]
    }
}

/*
 * ElTableColumn可用属性
 * 未找到方法直接导出tableColumn的props类型定义
 * https://element-plus.org/zh-CN/component/table.html#table-column-attributes
 */
interface ElTableColumn {
    type?: 'selection' | 'index' | 'expand'
    index?: number | Function
    label?: string
    'column-key'?: string
    prop?: string
    width?: string | number
    'min-width'?: string | number
    fixed?: string | boolean
    'render-header'?: Function
    sortable?: string | boolean
    'sort-method'?: Function
    'sort-by'?: Function
    'sort-orders'?: string[] | null[]
    resizable?: boolean
    formatter?: Function
    'show-overflow-tooltip'?: boolean
    align?: 'left' | 'center' | 'right'
    'header-align'?: 'left' | 'center' | 'right'
    'class-name'?: string
    'label-class-name'?: string
    selectable?: Function
    'reserve-selection'?: boolean
    filters?: { key: string; value: string }[]
    'filter-placement'?: string
    'filter-multiple'?: boolean
    'filter-method'?: Function
    'filtered-value'?: any[]
}
