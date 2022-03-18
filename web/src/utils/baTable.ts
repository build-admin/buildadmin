import { reactive, onMounted, onUnmounted } from 'vue'
import { getArrayKey } from '/@/utils/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { baTableApi } from '/@/api/common'

export default class baTable {
    public api
    public controllerUrl

    /* 表格状态-s */
    public table: Table = reactive({
        // 主键字段
        pk: 'id',
        // 数据源
        data: [],
        // 路由remark
        remark: null,
        // 表格加载状态
        loading: false,
        // 选中项
        selection: [],
        // 不需要'双击编辑'的字段
        dblClickNotEditColumn: [undefined],
        // 列数据
        column: [],
        // 数据总量
        total: 0,
        // 字段搜索,快速搜索,分页等数据
        filter: {},
    })
    /* 表格状态-e */

    /* 表单状态-s */
    public form: Form = reactive({
        // 表单label宽度
        labelWidth: 160,
        // 当前操作:add=添加,edit=编辑
        operate: '',
        // 被操作数据ID,支持批量编辑:add=[0],edit=[1,2,n]
        operateIds: [],
        // 表单数据
        items: {},
        // 提交按钮状态
        submitLoading: false,
        // 默认表单数据(添加)
        defaultItems: {},
    })
    /* 表单状态-e */

    constructor(controllerUrl: string, table: Table, form: Form = {}) {
        this.controllerUrl = controllerUrl
        this.form = Object.assign(this.form, form)
        this.table = Object.assign(this.table, table)

        this.api = new baTableApi(controllerUrl)
    }

    /* API请求方法-s */
    // 查看
    getIndex() {
        this.table.loading = true
        return this.api
            .index(this.table.filter)
            .then((res) => {
                this.table.data = res.data.list
                this.table.total = res.data.total
                this.table.remark = res.data.remark
                this.table.loading = false
            })
            .catch(() => {
                this.table.loading = false
            })
    }
    // 删除
    postDel(ids: string[]) {
        this.api.del(ids).then((res) => {
            this.onTableHeaderAction('refresh', {})
        })
    }
    // 编辑
    requestEdit(id: string) {
        this.form.items = {}
        return this.api
            .edit({
                id: id,
            })
            .then((res) => {
                this.form.items = res.data.row
            })
    }
    /* API请求方法-e */

    /**
     * 双击表格
     */
    onTableDblclick = (row: TableRow, column: any) => {
        if (this.table.dblClickNotEditColumn!.indexOf(column.property) === -1) {
            this.toggleForm('edit', [row[this.table.pk!]])
        }
    }

    /**
     * 打开表单
     * @param operate 操作:add=添加,edit=编辑
     * @param operateIds 被操作项的数组:add=[],edit=[1,2,...]
     */
    toggleForm = (operate: string = '', operateIds: string[] = []) => {
        if (operate == 'edit') {
            if (!operateIds.length) {
                return false
            }
            this.requestEdit(operateIds[0])
        } else if (operate == 'add') {
            this.form.items = this.form.defaultItems!
        }
        this.form.operate = operate
        this.form.operateIds = operateIds
    }

    onSubmit = () => {
        this.form.submitLoading = true
        this.api
            .postData(this.form.operate!, this.form.items!)
            .then((res) => {
                this.onTableHeaderAction('refresh', {})
                this.form.submitLoading = false
                this.form.operateIds?.shift()
                if (this.form.operateIds?.length! > 0) {
                    this.toggleForm('edit', this.form.operateIds)
                } else {
                    this.toggleForm()
                }
            })
            .catch((err) => {
                this.form.submitLoading = false
            })
    }

    /* 获取表格选择项的id数组 */
    getSelectionIds() {
        let ids: string[] = []
        for (const key in this.table.selection) {
            ids.push(this.table.selection[key as any][this.table.pk!])
        }
        return ids
    }

    /**
     * 表格内的事件响应
     * @param event 事件:selection-change=选中项改变,page-size-change=每页数量改变,current-page-change=翻页,sort-change=排序
     * @param data 携带数据
     */
    onTableAction = (event: string, data: anyObj) => {
        const actionFun = new Map([
            [
                'selection-change',
                () => {
                    this.table.selection = data as TableRow[]
                },
            ],
            [
                'page-size-change',
                () => {
                    this.table.filter!.limit = data.size
                    this.getIndex()
                },
            ],
            [
                'current-page-change',
                () => {
                    this.table.filter!.page = data.page
                    this.getIndex()
                },
            ],
            [
                'sort-change',
                () => {
                    let newOrder = ''
                    if (!data.prop) {
                        newOrder = ''
                    } else if (data.prop) {
                        newOrder = data.prop + ',' + data.order
                    }
                    if (newOrder != this.table.filter!.order) {
                        this.table.filter!.order = newOrder
                        this.getIndex()
                    }
                },
            ],
        ])

        let action = actionFun.get(event) || actionFun.get('default')
        return action!.call(this)
    }

    /**
     * 表格顶栏按钮事件响应
     * @param event 事件:refresh=刷新,edit=编辑,delete=删除,quick-search=快速查询
     * @param data 携带数据
     */
    onTableHeaderAction = (event: string, data: anyObj) => {
        const actionFun = new Map([
            [
                'refresh',
                () => {
                    this.table.data = []
                    this.getIndex()
                },
            ],
            [
                'edit',
                () => {
                    this.toggleForm('edit', this.getSelectionIds())
                },
            ],
            [
                'delete',
                () => {
                    this.postDel(this.getSelectionIds())
                },
            ],
            [
                'quick-search',
                () => {
                    this.table.filter!.quick_search = data.keyword
                    this.getIndex()
                },
            ],
            [
                'change-show-column',
                () => {
                    let columnKey = getArrayKey(this.table.column, 'prop', data.field)
                    this.table.column[columnKey].show = data.value
                },
            ],
            [
                'default',
                () => {
                    console.log('未定义操作')
                },
            ],
        ])

        let action = actionFun.get(event) || actionFun.get('default')
        return action!.call(this)
    }

    mount() {
        const { proxy } = useCurrentInstance()
        onMounted(() => {
            /**
             * 表格内的按钮响应
             * @param name 按钮name
             * @param row 被操作行数据
             */
            proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
                if (data.name == 'edit') {
                    this.toggleForm('edit', [data.row[this.table.pk!]])
                } else if (data.name == 'delete') {
                    this.postDel([data.row[this.table.pk!]])
                }
            })

            /**
             * 通用搜索响应
             * @param comSearchData 通用搜索数据
             */
            proxy.eventBus.on('onTableComSearch', (data: comSearchData) => {
                this.table.filter!.search = data
                this.getIndex()
            })
        })

        onUnmounted(() => {
            proxy.eventBus.off('onTableComSearch')
            proxy.eventBus.off('onTableButtonClick')
        })
    }
}

interface Table {
    pk?: string
    data?: TableRow[]
    remark?: string | null
    loading?: boolean
    selection?: TableRow[]
    dblClickNotEditColumn?: (string | undefined)[]
    column: TableColumn[]
    total?: number
    filter?: anyObj
    defaultOrder?: { prop: string; order: string }
}

interface Form {
    labelWidth?: number
    operate?: string
    operateIds?: string[]
    items?: anyObj
    submitLoading?: boolean
    defaultItems?: anyObj
}
