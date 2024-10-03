import type { ButtonProps, ButtonType, ColProps, FormInstance, PopconfirmProps, TableColumnCtx, TagProps } from 'element-plus'
import type { Mutable } from 'element-plus/es/utils'
import type { Component, ComponentPublicInstance } from 'vue'
import Table from '/@/components/table/index.vue'

declare global {
    interface BaTable {
        // 表格 ref，通常在 页面 onMounted 时赋值，可选的
        ref?: typeof Table
        // 表格对应数据表的主键字段
        pk?: string
        // 表格数据，通过 getIndex 加载
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
        // 字段搜索，快速搜索，分页等数据
        filter?: {
            page?: number
            limit?: number
            order?: string
            quickSearch?: string
            search?: comSearchData[]
            [key: string]: any
        }
        // 默认排序字段和排序方式
        defaultOrder?: { prop: string; order: string }
        // 拖动排序限位字段，例如拖动行 pid=1，那么拖动目的行 pid 也需要为 1
        dragSortLimitField?: string
        // 接受 url 的 query 参数并自动触发通用搜索
        acceptQuery?: boolean
        // 显示公共搜索
        showComSearch?: boolean
        // 不需要 '双击编辑' 的字段，type=selection 的列为 undefined
        dblClickNotEditColumn?: (string | undefined)[]
        // 是否展开所有子项，树状表格专用属性
        expandAll?: boolean
        // 当前表格所在页面的路由 path
        routePath?: string
        // 表格扩展数据，随意定义，以便一些自定义数据可以随 baTable 实例传递
        extend?: anyObj
    }

    interface TableRenderPublicInstance extends ComponentPublicInstance {
        $attrs: {
            renderValue: any
            renderRow: TableRow
            renderField: TableColumn
            renderColumn: TableColumnCtx<TableRow>
            renderIndex: number
        }
    }

    interface BaTableForm {
        // 表单 ref，实例化表格时通常无需传递
        ref?: FormInstance | undefined
        // 表单项 label 的宽度
        labelWidth?: number
        // 当前操作:add=添加,edit=编辑
        operate?: string
        // 被操作数据ID，支持批量编辑:add=[0],edit=[1,2,n]
        operateIds?: string[]
        // 表单数据，内含用户输入
        items?: anyObj
        // 提交按钮状态
        submitLoading?: boolean
        // 默认表单数据（添加时）
        defaultItems?: anyObj
        // 表单加载状态
        loading?: boolean
        // 表单扩展数据，随意定义，以便一些自定义数据可以随 baTable 实例传递
        extend?: anyObj
    }

    /**
     * BaTable 前置处理函数（前置埋点）
     */
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

    /**
     * BaTable 后置处理函数（后置埋点）
     */
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

    /**
     * 表格列
     */
    interface TableColumn extends Partial<TableColumnCtx<TableRow>> {
        // 是否显示
        show?: boolean
        // 渲染为 \components\table\fieldRender\ 中的组件（单元格渲染器）之一，填写组件名即可
        render?: tableRenderer
        // 自定义插槽渲染（render: 'slot'）时，slot 的名称
        slotName?: string
        // 自定义组件/函数渲染（render: 'customRender'）时，要渲染的组件或已注册组件名称的字符串
        customRender?: string | Component
        // 渲染为 tag 时 el-tag 组件的 effect
        effect?: TagProps['effect']
        // 渲染为 tag 时 el-tag 组件的 size
        size?: TagProps['size']
        // 单元格渲染器需要的自定义数据，比如（render: 'tag'）时，可以指定不同值时的 tag 的 type 属性 { open: 'success', close: 'info' }
        custom?: any
        // 渲染为链接时，链接的打开方式
        target?: aTarget
        // 渲染为 datetime 时的格式化方式，字母可以自由组合:y=年,m=月,d=日,h=时,M=分,s=秒，默认：yyyy-mm-dd hh:MM:ss
        timeFormat?: string
        // 默认值（单元格值为 undefined 或 null 时取默认值，仅使用了 render 时有效）
        default?: any
        // 值替换数据，如 { open: '开', close: '已关闭' }
        replaceValue?: any
        // 操作按钮组
        buttons?: OptButton[]
        // 是否允许动态控制字段是否显示，默认为 true
        enableColumnDisplayControl?: boolean
        // 通用搜索操作符，默认值为=，值为 false 禁用此字段通用搜索，支持的操作符见下类型定义
        operator?: boolean | OperatorStr
        // 通用搜索框的 placeholder
        operatorPlaceholder?: string
        // 公共搜索渲染方式，render=tag|switch 时公共搜索也会渲染为下拉，数字会渲染为范围筛选，时间渲染为时间选择器等
        comSearchRender?: 'remoteSelect' | 'select' | 'date' | 'customRender' | 'slot'
        // 公共搜索自定义组件/函数渲染
        comSearchCustomRender?: string | Component
        // 公共搜索自定义渲染为 slot 时，slot 的名称
        comSearchSlotName?: string
        // 公共搜索自定义渲染时，外层 el-col 的属性（仅 customRender、slot支持）
        comSearchColAttr?: Partial<ColProps>
        // 公共搜索，是否显示字段的 label
        comSearchShowLabel?: boolean
        // 远程属性
        remote?: {
            pk?: string
            field?: string
            params?: anyObj
            multiple?: boolean
            remoteUrl: string
        }
        // 谨慎使用：自定义渲染模板，方法可返回 html 内容，请确保返回内容是 xss 安全的
        customTemplate?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => string
        // 单元格渲染组件的 key，此 key 值改变时单元格将自动重新渲染，默认将根据列配置等属性自动生成
        getRenderKey?: (row: TableRow, field: TableColumn, column: TableColumnCtx<TableRow>, index: number) => string
        // 使用了 render 属性时，渲染前对字段值的预处理方法（即将废弃，请使用兼容 el-table 的 formatter 函数代替）
        renderFormatter?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
        // 渲染为 url 时的点击事件（即将废弃，请使用 el-table 的 @cell-click 或单元格自定义渲染代替）
        click?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
    }

    /**
     * 表格右侧操作按钮
     */
    interface OptButton {
        // 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮,basicButton=普通按钮
        render: 'tipButton' | 'confirmButton' | 'moveButton' | 'basicButton'
        name: string
        title?: string
        text?: string
        class?: string
        type: ButtonType
        icon: string
        popconfirm?: Partial<Mutable<PopconfirmProps>>
        disabledTip?: boolean
        // 自定义点击事件
        click?: (row: TableRow, field: TableColumn) => void
        // 按钮是否显示，请返回布尔值，比如：display: auth('add')
        display?: (row: TableRow, field: TableColumn) => boolean
        // 按钮是否禁用，请返回布尔值
        disabled?: (row: TableRow, field: TableColumn) => boolean
        // 自定义其他 el-button 的属性
        attr?: Partial<Mutable<ButtonProps>>
    }

    /**
     * 表格行
     */
    interface TableRow extends anyObj {
        children?: TableRow[]
    }

    /**
     * 表头支持的按钮
     */
    type HeaderOptButton = 'refresh' | 'add' | 'edit' | 'delete' | 'unfold' | 'comSearch' | 'quickSearch' | 'columnDisplay'

    /**
     * 通用搜索操作符支持的值
     */
    type OperatorStr =
        | 'eq' // 等于，默认值
        | 'ne' // 不等于
        | 'gt' // 大于
        | 'egt' // 大于等于
        | 'lt' // 小于
        | 'elt' // 小于等于
        | 'LIKE'
        | 'NOT LIKE'
        | 'IN'
        | 'NOT IN'
        | 'RANGE' // 范围，将生成两个输入框，可以输入最小值和最大值
        | 'NOT RANGE'
        | 'NULL' // 是否为NULL，将生成单个复选框
        | 'NOT NULL'
        | 'FIND_IN_SET'
        // 不推荐使用的，因为部分符号不利于网络传输
        | '='
        | '<>'
        | '>'
        | '>='
        | '<'
        | '<='

    /**
     * 链接打开方式
     */
    type aTarget = '_blank' | '_self'

    /**
     * 通用搜索事件返回的 Data
     */
    interface comSearchData {
        field: string
        val: string | string[] | number | number[]
        operator: string
        render?: string
    }

    interface ElTreeData {
        label: string
        children?: ElTreeData[]
    }
}
