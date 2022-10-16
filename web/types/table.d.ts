import type { TagProps, ButtonType, ElForm, FormInstance } from 'element-plus'
import { Component, ComponentPublicInstance } from 'vue'
import Table from '/@/components/table/index.vue'
import { TableColumnCtx } from 'element-plus/es/components/table/src/table-column/defaults'

declare global {
    /* baTable */
    interface BaTable {
        // 表格 ref，通常在 页面onMounted 时赋值
        ref?: typeof Table
        // 表格对应数据表的主键字段
        pk?: string
        // 表格数据，通过getIndex加载
        data?: TableRow[]
        // 路由 remark，后台菜单规则备注信息
        remark?: string | null
        // 表格加载状态
        loading?: boolean
        // 当前选中行
        selection?: TableRow[]
        // 表格列定义
        column: TableColumn[]
        // 数据总量
        total?: number
        // 字段搜索,快速搜索,分页等数据
        filter?: {
            page?: number
            limit?: number
            order?: string
            quick_search?: string
            search?: comSearchData[]
            [key: string]: any
        }
        // 默认排序字段和排序方式
        defaultOrder?: { prop: string; order: string }
        // 拖动排序限位字段:例如拖动行pid=1,那么拖动目的行pid也需要为1
        dragSortLimitField?: string
        // 接受url的query参数并自动触发通用搜索
        acceptQuery?: boolean
        // 显示公共搜索
        showComSearch?: boolean
        // 不需要'双击编辑'的字段，type=selection的列为 undefined
        dblClickNotEditColumn?: (string | undefined)[]
        // 是否展开所有子项，树状表格专用属性
        expandAll?: boolean
        // 表格扩展数据，随意定义，以便一些自定义数据可以随baTable实例传递
        extend?: anyObj
    }

    interface TableRenderPublicInstance extends ComponentPublicInstance {
        $attrs: {
            renderValue: any
            renderRow: TableRow
            renderField: TableColumn
        }
    }

    /* baTableForm */
    interface BaTableForm {
        // 表单ref，实例化表格时通常无需传递
        ref?: InstanceType<typeof ElForm> | undefined
        // 表单项label的宽度
        labelWidth?: number
        // 当前操作:add=添加,edit=编辑
        operate?: string
        // 被操作数据ID,支持批量编辑:add=[0],edit=[1,2,n]
        operateIds?: string[]
        // 表单数据，内含用户输入
        items?: anyObj
        // 提交按钮状态
        submitLoading?: boolean
        // 默认表单数据(添加时)
        defaultItems?: anyObj
        // 表单加载状态
        loading?: boolean
        // 表单扩展数据，随意定义，以便一些自定义数据可以随baTable实例传递
        extend?: anyObj
    }

    /* BaTable前置处理函数(前置埋点) */
    interface BaTableBefore {
        // 获取表格数据前
        getIndex?: () => boolean | void
        // 删除前
        postDel?: ({ ids }: { ids: string[] }) => boolean | void
        // 编辑请求前
        requestEdit?: ({ id }: { id: string }) => boolean | void
        // 双击表格具体操作执行前
        onTableDblclick?: ({ row, column }: { row: TableRow; column: TableColumn }) => boolean | void
        // 表单切换前
        toggleForm?: ({ operate, operateIds }: { operate: string; operateIds: string[] }) => boolean | void
        // 表单提交前
        onSubmit?: ({ formEl, operate, items }: { formEl: FormInstance | undefined; operate: string; items: anyObj }) => boolean | void
        // 表格内事件响应前
        onTableAction?: ({ event, data }: { event: string; data: anyObj }) => boolean | void
        // 表格顶部菜单事件响应前
        onTableHeaderAction?: ({ event, data }: { event: string; data: anyObj }) => boolean | void
        // 表格初始化前
        mount?: () => boolean | void
        [key: string]: Function | undefined
    }

    /* BaTable后置处理函数(后置埋点) */
    interface BaTableAfter {
        // 表格数据请求后
        getIndex?: ({ res }: { res: ApiResponse }) => void
        // 删除请求后
        postDel?: ({ res }: { res: ApiResponse }) => void
        // 编辑表单请求后
        requestEdit?: ({ res }: { res: ApiResponse }) => void
        // 双击单元格操作执行后
        onTableDblclick?: ({ row, column }: { row: TableRow; column: TableColumn }) => void
        // 表单切换后
        toggleForm?: ({ operate, operateIds }: { operate: string; operateIds: string[] }) => void
        // 表单提交后
        onSubmit?: ({ res }: { res: ApiResponse }) => void
        // 表格事件响应后
        onTableAction?: ({ event, data }: { event: string; data: anyObj }) => void
        // 表格顶部事件菜单响应后
        onTableHeaderAction?: ({ event, data }: { event: string; data: anyObj }) => void
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
        // 是否在下拉菜单的复选框显示 默认为true显示
        enableColumnDisplayControl?: boolean
        // 渲染为:icon|switch|image|images|tag|url|datetime|buttons|customTemplate|customRender
        render?: 'icon' | 'switch' | 'image' | 'images' | 'tag' | 'tags' | 'url' | 'datetime' | 'buttons' | 'customTemplate' | 'customRender'
        // 操作按钮组
        buttons?: OptButton[]
        // 渲染为Tag时:el-tag 组件的主题
        effect?: TagProps['effect']
        // 渲染为Tag时:el-tag 组件的size
        size?: TagProps['size']
        // 自定义数据:比如渲染为Tag时,可以指定不同值时的Tag的Type属性 { open: 'success', close: 'info' }
        custom?: any
        // 谨慎使用：自定义渲染模板，方法可返回html内容，请确保返回内容是xss安全的
        customTemplate?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => string
        // 自定义组件/函数渲染
        customRender?: string | Component
        // 渲染为链接时,链接的打开方式
        target?: aTarget
        // 渲染为:url 时的点击事件
        click?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
        // 渲染为 datetime 时的格式化方式,字母可以自由组合:y=年,m=月,d=日,h=时,M=分,s=秒，默认：yyyy-mm-dd hh:MM:ss
        timeFormat?: string
        // 默认值
        default?: any
        // 值替换数据,如{open: '开'}
        replaceValue?: any
        // 使用了 render 属性时,渲染前对字段值的预处理方法，请返回新值
        renderFormatter?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
        // 通用搜索操作符,默认值为=,false禁用此字段通用搜索,支持的操作符见下定义
        operator?: boolean | OperatorStr
        // 通用搜索框的placeholder
        operatorPlaceholder?: string
        // 公共搜索渲染方式:上方的 render=tag|switch 时公共搜索也会渲染为下拉，数字会渲染为范围筛选，时间渲染为时间选择器等
        comSearchRender?: 'remoteSelect' | 'select' | 'date' | 'customRender'
        // 公共搜索自定义组件/函数渲染
        comSearchCustomRender?: string | Component
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
        // 自定义点击事件
        click?: (row: TableRow, field: TableColumn) => void
        // 按钮是否显示，请返回布尔值
        display?: (row: TableRow, field: TableColumn) => boolean
    }

    /* 表格行 */
    interface TableRow extends anyObj {
        children?: TableRow[]
    }

    /* 表头支持的按钮 */
    type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'recycle bin' | 'comSearch' | 'quickSearch' | 'columnDisplay'

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
