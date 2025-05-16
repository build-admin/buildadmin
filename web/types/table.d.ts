import type {
    ButtonProps,
    ButtonType,
    ColProps,
    ElTooltipProps,
    FormInstance,
    ImageProps,
    PopconfirmProps,
    SwitchProps,
    TableColumnCtx,
    TagProps,
} from 'element-plus'
import type { Mutable } from 'element-plus/es/utils'
import type { Component, ComponentPublicInstance } from 'vue'
import Icon from '/@/components/icon/index.vue'
import Table from '/@/components/table/index.vue'

declare global {
    interface BaTable {
        /**
         * 表格数据，通过 baTable.getData 获取
         * 刷新数据可使用 baTable.onTableHeaderAction('refresh', { event: 'custom' })
         */
        data?: TableRow[]

        /**
         * 表格列定义
         */
        column: TableColumn[]

        /**
         * 获取表格数据时的过滤条件（含公共搜索、快速搜索、分页、排序等数据）
         * 公共搜索数据可使用 baTable.setComSearchData 和 baTable.getComSearchData 进行管理
         */
        filter?: {
            page?: number
            limit?: number
            order?: string
            quickSearch?: string
            search?: comSearchData[]
            [key: string]: any
        }

        /**
         * 不需要双击编辑的字段，type=selection 的列为 undefined
         * 禁用全部列的双击编辑，可使用 ['all']
         */
        dblClickNotEditColumn?: (string | undefined)[]

        /**
         * 表格扩展数据，随意定义，以便一些自定义数据可以随 baTable 实例传递
         */
        extend?: anyObj

        // 表格 ref，通常在 页面 onMounted 时赋值，可选的
        ref?: InstanceType<typeof Table> | null
        // 表格对应数据表的主键字段
        pk?: string
        // 路由 remark，后台菜单规则备注信息
        remark?: string | null
        // 表格加载状态
        loading?: boolean
        // 当前选中行
        selection?: TableRow[]
        // 数据总量
        total?: number
        // 默认排序字段和排序方式
        defaultOrder?: { prop: string; order: string }
        // 拖动排序限位字段，例如拖动行 pid=1，那么拖动目的行 pid 也需要为 1
        dragSortLimitField?: string
        // 接受 url 的 query 参数并自动触发公共搜索
        acceptQuery?: boolean
        // 显示公共搜索
        showComSearch?: boolean
        // 是否展开所有子项，树状表格专用属性
        expandAll?: boolean
        // 当前表格所在页面的路由 path
        routePath?: string
    }

    interface BaTableForm {
        /**
         * 当前表单项数据
         */
        items?: anyObj

        /**
         * 当前操作标识:Add=添加,Edit=编辑
         */
        operate?: string

        /**
         * 添加表单字段默认值，打开表单时会使用 cloneDeep 赋值给 this.form.items 对象
         */
        defaultItems?: anyObj

        /**
         * 表单扩展数据，可随意定义，以便一些自定义数据可以随 baTable 实例传递
         */
        extend?: anyObj

        // 表单 ref，实例化表格时通常无需传递
        ref?: FormInstance | undefined
        // 表单项 label 的宽度
        labelWidth?: number
        // 被操作数据ID，支持批量编辑:add=[0],edit=[1,2,n]
        operateIds?: string[]
        // 提交按钮状态
        submitLoading?: boolean
        // 表单加载状态
        loading?: boolean
    }

    /**
     * BaTable 前置处理函数（前置埋点）
     */
    interface BaTableBefore {
        /**
         * 获取表格数据前的钩子（返回 false 可取消原操作）
         */
        getData?: () => boolean | void

        /**
         * 删除前的钩子（返回 false 可取消原操作）
         * @param object.ids 被删除数据的主键集合
         */
        postDel?: ({ ids }: { ids: string[] }) => boolean | void

        /**
         * 获取被编辑行数据前的钩子（返回 false 可取消原操作）
         * @param object.id 被编辑行主键
         */
        getEditData?: ({ id }: { id: string }) => boolean | void

        /**
         * 双击表格具体操作执行前钩子（返回 false 可取消原操作）
         * @param object.row 被双击行数据
         * @param object.column 被双击列数据
         */
        onTableDblclick?: ({ row, column }: { row: TableRow; column: TableColumn }) => boolean | void

        /**
         * 表单切换前钩子（返回 false 可取消默认行为）
         * @param object.operate 当前操作标识:Add=添加,Edit=编辑
         * @param object.operateIds 被操作的行 ID 集合
         */
        toggleForm?: ({ operate, operateIds }: { operate: string; operateIds: string[] }) => boolean | void

        /**
         * 表单提交前钩子（返回 false 可取消原操作）
         * @param object.formEl 表单组件ref
         * @param object.operate 当前操作标识:Add=添加,Edit=编辑
         * @param object.items 表单数据
         */
        onSubmit?: ({ formEl, operate, items }: { formEl?: FormInstance | null; operate: string; items: anyObj }) => boolean | void

        /**
         * 表格内事件响应前钩子（返回 false 可取消原操作）
         * @param object.event 事件名称
         * @param object.data 事件携带的数据
         */
        onTableAction?: ({ event, data }: { event: string; data: anyObj }) => boolean | void

        /**
         * 表格顶部菜单事件响应前钩子（返回 false 可取消原操作）
         * @param object.event 事件名称
         * @param object.data 事件携带的数据
         */
        onTableHeaderAction?: ({ event, data }: { event: string; data: anyObj }) => boolean | void

        /**
         * 表格初始化前钩子
         */
        mount?: () => boolean | void

        /** getData 的别名 */
        getIndex?: () => boolean | void
        /** getEditData 的别名 */
        requestEdit?: ({ id }: { id: string }) => boolean | void

        // 可自定义其他钩子
        [key: string]: Function | undefined
    }

    /**
     * BaTable 后置处理函数（后置埋点）
     */
    interface BaTableAfter {
        /**
         * 请求到表格数据后钩子
         * 此时 baTable.table.data 已赋值
         * @param object.res 请求完整响应
         */
        getData?: ({ res }: { res: ApiResponse }) => void

        /**
         * 删除请求后钩子
         * @param object.res 请求完整响应
         */
        postDel?: ({ res }: { res: ApiResponse }) => void

        /**
         * 获取到编辑行数据后钩子
         * 此时 baTable.form.items 已赋值
         * @param object.res 请求完整响应
         */
        getEditData?: ({ res }: { res: ApiResponse }) => void

        /**
         * 双击单元格操作执行后钩子
         * @param object.row 当前行数据
         * @param object.column 当前列数据
         */
        onTableDblclick?: ({ row, column }: { row: TableRow; column: TableColumn }) => void

        /**
         * 表单切换后钩子
         * @param object.operate 当前操作标识:Add=添加,Edit=编辑
         * @param object.operateIds 被操作的 ID 集合
         */
        toggleForm?: ({ operate, operateIds }: { operate: string; operateIds: string[] }) => void

        /**
         * 表单提交后钩子
         * @param object.res 请求完整响应
         */
        onSubmit?: ({ res }: { res: ApiResponse }) => void

        /**
         * 表格内事件响应后钩子
         * @param object.event 事件名称
         * @param object.data 事件携带的数据
         */
        onTableAction?: ({ event, data }: { event: string; data: anyObj }) => void

        /**
         * 表格顶部菜单事件响应后钩子
         * @param object.event 事件名称
         * @param object.data 事件携带的数据
         */
        onTableHeaderAction?: ({ event, data }: { event: string; data: anyObj }) => void

        /** getData 的别名 */
        getIndex?: ({ res }: { res: ApiResponse }) => void
        /** getEditData 的别名 */
        requestEdit?: ({ res }: { res: ApiResponse }) => void

        // 可自定义其他钩子
        [key: string]: Function | undefined
    }

    /**
     * 表格公共搜索数据
     */
    interface ComSearch {
        /** 表单项数据 */
        form: anyObj
        /** 字段搜索配置，搜索操作符（operator）、字段渲染方式（render）等 */
        fieldData: Map<string, any>
    }

    /**
     * 表格列
     */
    interface TableColumn extends Partial<TableColumnCtx<TableRow>> {
        // 是否于表格显示此列
        show?: boolean
        // 渲染器组件名，即 \src\components\table\fieldRender\ 中的组件之一，也可以查看 TableRenderer 类型定义
        render?: TableRenderer
        // 值替换数据（字典数据），同时用于单元格渲染时和作为公共搜索下拉框数据，格式如：{ open: '开', close: '关', disable: '已禁用' }
        replaceValue?: Record<string, any>

        // render=slot 时，slot 的名称
        slotName?: string
        // render=customRender 时，要渲染的组件或已注册组件名称的字符串
        customRender?: string | Component
        // render=customTemplate 时，自定义渲染 html，应谨慎使用：请返回 html 内容，务必确保返回内容是 xss 安全的
        customTemplate?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => string
        // 渲染前对字段值的预处理函数（对 el-table 的 formatter 扩展）
        formatter?: (row: TableRow, column: TableColumnCtx<TableRow>, cellValue: any, index: number) => any

        /**
         * 自定义单元格渲染属性（比如单元格渲染器内部的 tag、button 组件的属性，设计上不仅是组件属性，也可以自定义其他渲染相关属性）
         * 直接定义对应组件的属性 object，或使用一个函数返回组件属性 object
         */
        customRenderAttr?: {
            tag?: TableContextDataFun<TagProps>
            icon?: TableContextDataFun<InstanceType<typeof Icon>['$props']>
            image?: TableContextDataFun<ImageProps>
            switch?: TableContextDataFun<SwitchProps>
            tooltip?: TableContextDataFun<ElTooltipProps>
            [key: string]: any
        }

        // render=tag 时，el-tag 组件的 effect
        effect?: TagProps['effect']
        // render=tag 时，el-tag 组件的 size
        size?: TagProps['size']
        // render=url 时，链接的打开方式
        target?: '_blank' | '_self'
        // render=datetime 时，时间日期的格式化方式，字母可以自由组合:y=年,m=月,d=日,h=时,M=分,s=秒，默认：yyyy-mm-dd hh:MM:ss
        timeFormat?: string
        // render=buttons 时，操作按钮数组
        buttons?: OptButton[]

        /**
         * 单元格渲染器需要的其他任意自定义数据
         * 1. render=tag 时，可单独指定每个不同的值 tag 的 type 属性 { open: 'success', close: 'info', disable: 'danger' }
         */
        custom?: any

        // 默认值（单元格值为 undefined,null,'' 时取默认值，仅使用了 render 时有效）
        default?: any
        // 是否允许动态控制字段是否显示，默认为 true
        enableColumnDisplayControl?: boolean
        // 单元格渲染组件的 key，默认将根据列配置等属性自动生成（此 key 值改变时单元格将自动重新渲染）
        getRenderKey?: (row: TableRow, field: TableColumn, column: TableColumnCtx<TableRow>, index: number) => string

        // 公共搜索操作符，默认值为 = ，值为 false 禁用此字段公共搜索，支持的操作符见下类型定义
        operator?: boolean | OperatorStr
        // 公共搜索框的 placeholder
        operatorPlaceholder?: string | string[]
        // 公共搜索渲染方式，render=tag|switch 时公共搜索也会渲染为下拉，数字会渲染为范围筛选，时间渲染为时间选择器等
        comSearchRender?: 'remoteSelect' | 'select' | 'date' | 'customRender' | 'slot'
        // 公共搜索自定义组件/函数渲染
        comSearchCustomRender?: string | Component
        // 公共搜索自定义渲染为 slot 时，slot 的名称
        comSearchSlotName?: string
        // 公共搜索自定义渲染时，外层 el-col 的属性（仅 customRender、slot 支持）
        comSearchColAttr?: Partial<ColProps>
        // 公共搜索是否显示字段的 label
        comSearchShowLabel?: boolean
        // 公共搜索渲染为远程下拉时，远程下拉组件的必要属性
        remote?: {
            pk?: string
            field?: string
            params?: anyObj
            multiple?: boolean
            remoteUrl: string
        }

        // 使用了 render 属性时，渲染前对字段值的预处理方法（即将废弃，请使用兼容 el-table 的 formatter 函数代替）
        renderFormatter?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
        // 渲染为 url 时的点击事件（即将废弃，请使用 el-table 的 @cell-click 或单元格自定义渲染代替）
        click?: (row: TableRow, field: TableColumn, value: any, column: TableColumnCtx<TableRow>, index: number) => any
    }

    /**
     * 表格右侧操作按钮
     */
    interface OptButton {
        /**
         * 渲染方式:tipButton=带tip的按钮,confirmButton=带确认框的按钮,moveButton=移动按钮,basicButton=普通按钮
         */
        render: 'tipButton' | 'confirmButton' | 'moveButton' | 'basicButton'

        /**
         * 按钮名称，将作为触发表格内事件（onTableAction）时的事件名
         */
        name: string

        /**
         * 鼠标 hover 时的提示
         * 可使用多语言翻译 key，比如 user.group
         */
        title?: string

        /**
         * 直接在按钮内显示的文字，title 有值时可为空
         * 可使用多语言翻译 key，比如 user.group
         */
        text?: string

        /**
         * 自定义按钮的点击事件
         * @param row 当前行数据
         * @param field 当前列数据
         */
        click?: (row: TableRow, field: TableColumn) => void

        /**
         * 按钮是否显示（请返回布尔值，比如：display: auth('add')）
         * @param row 当前行数据
         * @param field 当前列数据
         */
        display?: (row: TableRow, field: TableColumn) => boolean

        /**
         * 按钮是否禁用（请返回布尔值）
         * @param row 当前行数据
         * @param field 当前列数据
         */
        disabled?: (row: TableRow, field: TableColumn) => boolean

        /**
         * 自定义 el-button 的其他属性
         */
        attr?: Partial<Mutable<ButtonProps>>

        // 按钮 class
        class?: string
        // 按钮 type
        type: ButtonType
        // 按钮 icon 的名称
        icon: string
        // 确认按钮的气泡确认框的属性（el-popconfirm 的属性）
        popconfirm?: Partial<Mutable<PopconfirmProps>>
        // 是否禁用 title 提示，此值通常由系统动态调整以确保提示的显示效果
        disabledTip?: boolean
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
     * 公共搜索操作符支持的值
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
     * 公共搜索事件返回的 Data
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

    /**
     * 表格上下文数据
     */
    interface TableContextData {
        row?: TableRow
        field?: TableColumn
        cellValue?: any
        column?: TableColumnCtx<TableRow>
        index?: number
    }

    /**
     * 接受表格上下文数据的任意属性计算函数
     */
    type TableContextDataFun<T> = Partial<T> | ((context: TableContextData) => Partial<T>)

    interface TableRenderPublicInstance extends ComponentPublicInstance {
        $attrs: {
            renderValue: any
            renderRow: TableRow
            renderField: TableColumn
            renderColumn: TableColumnCtx<TableRow>
            renderIndex: number
        }
    }
}
