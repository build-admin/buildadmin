import { reactive, onUnmounted, onActivated, onDeactivated } from 'vue'
import { getArrayKey } from '/@/utils/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import type { baTableApi } from '/@/api/common'
import Sortable from 'sortablejs'
import { findIndexRow } from '/@/components/table'
import { ElNotification, ElForm } from 'element-plus'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'
import _ from 'lodash'
import { i18n } from '/@/lang/index'

export default class baTable {
    // API实例
    public api

    // 表格是否激活，多表格共存时，激活的表格才能触发事件
    public activate: boolean

    /* 表格状态-s 属性对应含义请查阅 BaTable 的类型定义 */
    public table: BaTable = reactive({
        ref: undefined,
        pk: 'id',
        data: [],
        remark: null,
        loading: false,
        selection: [],
        column: [],
        total: 0,
        filter: {},
        dragSortLimitField: 'pid',
        acceptQuery: true,
        showComSearch: false,
        dblClickNotEditColumn: [undefined],
        expandAll: false,
        extend: {},
    })
    /* 表格状态-e */

    /* 表单状态-s 属性对应含义请查阅 BaTableForm 的类型定义 */
    public form: BaTableForm = reactive({
        ref: undefined,
        labelWidth: 160,
        operate: '',
        operateIds: [],
        items: {},
        submitLoading: false,
        defaultItems: {},
        loading: false,
        extend: {},
    })
    /* 表单状态-e */

    // BaTable前置处理函数列表（前置埋点）
    public before: BaTableBefore

    // BaTable后置处理函数列表（后置埋点）
    public after: BaTableAfter

    // 通用搜索数据
    public comSearch: ComSearch = reactive({
        form: {},
        fieldData: new Map(),
    })

    constructor(api: baTableApi, table: BaTable, form: BaTableForm = {}, before: BaTableBefore = {}, after: BaTableAfter = {}) {
        this.api = api
        this.form = Object.assign(this.form, form)
        this.table = Object.assign(this.table, table)
        this.before = before
        this.after = after
        this.activate = true

        const route = useRoute()
        this.initComSearch(!_.isUndefined(route) ? route.query : {})
    }

    runBefore(funName: string, args: any = {}) {
        if (this.before && this.before[funName] && typeof this.before[funName] == 'function') {
            return this.before[funName]!({ ...args }) === false ? false : true
        }
        return true
    }

    runAfter(funName: string, args: any = {}) {
        if (this.after && this.after[funName] && typeof this.after[funName] == 'function') {
            return this.after[funName]!({ ...args }) === false ? false : true
        }
        return true
    }

    /* API请求方法-s */
    // 查看
    getIndex = () => {
        if (this.runBefore('getIndex') === false) return
        this.table.loading = true
        return this.api
            .index(this.table.filter)
            .then((res) => {
                this.table.data = res.data.list
                this.table.total = res.data.total
                this.table.remark = res.data.remark
                this.runAfter('getIndex', { res })
            })
            .finally(() => {
                this.table.loading = false
            })
    }
    // 删除
    postDel = (ids: string[]) => {
        if (this.runBefore('postDel', { ids }) === false) return
        this.api.del(ids).then((res) => {
            this.onTableHeaderAction('refresh', {})
            this.runAfter('postDel', { res })
        })
    }
    // 编辑
    requestEdit = (id: string) => {
        if (this.runBefore('requestEdit', { id }) === false) return
        this.form.loading = true
        this.form.items = {}
        return this.api
            .edit({
                [this.table.pk!]: id,
            })
            .then((res) => {
                this.form.loading = false
                this.form.items = res.data.row
                this.runAfter('requestEdit', { res })
            })
    }
    /* API请求方法-e */

    /**
     * 双击表格
     */
    onTableDblclick = (row: TableRow, column: any) => {
        if (!this.table.dblClickNotEditColumn!.includes('all') && !this.table.dblClickNotEditColumn!.includes(column.property)) {
            if (this.runBefore('onTableDblclick', { row, column }) === false) return
            this.toggleForm('edit', [row[this.table.pk!]])
            this.runAfter('onTableDblclick', { row, column })
        }
    }

    /**
     * 打开表单
     * @param operate 操作:add=添加,edit=编辑
     * @param operateIds 被操作项的数组:add=[],edit=[1,2,...]
     */
    toggleForm = (operate = '', operateIds: string[] = []) => {
        if (this.runBefore('toggleForm', { operate, operateIds }) === false) return
        if (this.form.ref) {
            this.form.ref.resetFields()
        }
        if (operate == 'edit') {
            if (!operateIds.length) {
                return false
            }
            this.requestEdit(operateIds[0])
        } else if (operate == 'add') {
            this.form.items = Object.assign({}, this.form.defaultItems)
        }
        this.form.operate = operate
        this.form.operateIds = operateIds
        this.runAfter('toggleForm', { operate, operateIds })
    }

    onSubmit = (formEl: InstanceType<typeof ElForm> | undefined = undefined) => {
        if (this.runBefore('onSubmit', { formEl: formEl, operate: this.form.operate!, items: this.form.items! }) === false) return

        Object.keys(this.form.items!).forEach((item) => {
            if (this.form.items![item] === null) delete this.form.items![item]
        })

        // 表单验证通过后执行的api请求操作
        const submitCallback = () => {
            this.form.submitLoading = true
            this.api
                .postData(this.form.operate!, this.form.items!)
                .then((res) => {
                    this.onTableHeaderAction('refresh', {})
                    this.form.operateIds?.shift()
                    if (this.form.operateIds!.length > 0) {
                        this.toggleForm('edit', this.form.operateIds)
                    } else {
                        this.toggleForm()
                    }
                    this.runAfter('onSubmit', { res })
                })
                .finally(() => {
                    this.form.submitLoading = false
                })
        }

        if (formEl) {
            this.form.ref = formEl
            formEl.validate((valid) => {
                if (valid) {
                    submitCallback()
                }
            })
        } else {
            submitCallback()
        }
    }

    /**
     * 获取表格选择项的id数组
     */
    getSelectionIds() {
        const ids: string[] = []
        this.table.selection?.forEach((item) => {
            ids.push(item[this.table.pk!])
        })
        return ids
    }

    /**
     * 表格内的事件统一响应
     * @param event 事件:selection-change=选中项改变,page-size-change=每页数量改变,current-page-change=翻页,sort-change=排序
     * @param data 携带数据
     */
    onTableAction = (event: string, data: anyObj) => {
        if (this.runBefore('onTableAction', { event, data }) === false) return
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
                    let newOrder: string | undefined
                    if (data.prop && data.order) {
                        newOrder = data.prop + ',' + data.order
                    }
                    if (newOrder != this.table.filter!.order) {
                        this.table.filter!.order = newOrder
                        this.getIndex()
                    }
                },
            ],
            [
                'default',
                () => {
                    console.warn('No action defined')
                },
            ],
        ])

        const action = actionFun.get(event) || actionFun.get('default')
        action!.call(this)
        return this.runAfter('onTableAction', { event, data })
    }

    /**
     * 表格顶栏按钮事件统一响应
     * @param event 事件:refresh=刷新,edit=编辑,delete=删除,quick-search=快速查询,unfold=折叠/展开,change-show-column=调整列显示状态
     * @param data 携带数据
     */
    onTableHeaderAction = (event: string, data: anyObj) => {
        if (this.runBefore('onTableHeaderAction', { event, data }) === false) return
        const actionFun = new Map([
            [
                'refresh',
                () => {
                    this.table.data = []
                    this.getIndex()
                },
            ],
            [
                'add',
                () => {
                    this.toggleForm('add')
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
                'unfold',
                () => {
                    if (!this.table.ref) {
                        console.warn('Collapse/expand failed because table ref is not defined. Please assign table ref when onMounted')
                        return
                    }
                    this.table.expandAll = data.unfold
                    this.table.ref.unFoldAll(data.unfold)
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
                    const columnKey = getArrayKey(this.table.column, 'prop', data.field)
                    this.table.column[columnKey].show = data.value
                },
            ],
            [
                'default',
                () => {
                    console.warn('No action defined')
                },
            ],
        ])

        const action = actionFun.get(event) || actionFun.get('default')
        action!.call(this)
        return this.runAfter('onTableHeaderAction', { event, data })
    }

    /**
     * 初始化默认排序
     * el表格的`default-sort`在自定义排序时无效
     * 此方法只有在表格数据请求结束后执行有效
     */
    initSort = () => {
        if (this.table.defaultOrder && this.table.defaultOrder.prop) {
            if (!this.table.ref) {
                console.warn('Failed to initialize default sorting because table ref is not defined. Please assign table ref when onMounted')
                return
            }

            const defaultOrder = this.table.defaultOrder.prop + ',' + this.table.defaultOrder.order
            if (this.table.filter && this.table.filter.order != defaultOrder) {
                this.table.filter.order = defaultOrder
                this.table.ref.getRef()?.sort(this.table.defaultOrder.prop, this.table.defaultOrder.order == 'desc' ? 'descending' : 'ascending')
            }
        }
    }

    /**
     * 表格拖动排序
     */
    dragSort = () => {
        const buttonsKey = getArrayKey(this.table.column, 'render', 'buttons')
        const moveButton = getArrayKey(this.table.column[buttonsKey]?.buttons, 'render', 'moveButton')
        if (moveButton === false) {
            return
        }
        if (!this.table.ref) {
            console.warn('Failed to initialize drag sort because table ref is not defined. Please assign table ref when onMounted')
            return
        }

        const el = this.table.ref.getRef().$el.querySelector('.el-table__body-wrapper .el-table__body tbody')
        Sortable.create(el, {
            animation: 200,
            handle: '.table-row-weigh-sort',
            ghostClass: 'ba-table-row',
            onStart: () => {
                this.table.column[buttonsKey].buttons?.forEach((item) => {
                    item.disabledTip = true
                })
            },
            onEnd: (evt: Sortable.SortableEvent) => {
                this.table.column[buttonsKey].buttons?.forEach((item) => {
                    item.disabledTip = false
                })
                // 找到对应行id
                const moveRow = findIndexRow(this.table.data!, evt.oldIndex!) as TableRow
                const replaceRow = findIndexRow(this.table.data!, evt.newIndex!) as TableRow
                if (this.table.dragSortLimitField && moveRow[this.table.dragSortLimitField] != replaceRow[this.table.dragSortLimitField]) {
                    this.onTableHeaderAction('refresh', {})
                    ElNotification({
                        type: 'error',
                        message: i18n.global.t('utils.The moving position is beyond the movable range!'),
                    })
                    return
                }

                this.api.sortableApi(moveRow[this.table.pk!], replaceRow[this.table.pk!]).then(() => {
                    this.onTableHeaderAction('refresh', {})
                })
            },
        })
    }

    mount = () => {
        if (this.runBefore('mount') === false) return
        this.activate = true
        const { proxy } = useCurrentInstance()
        /**
         * 表格内的按钮响应
         * @param name 按钮name
         * @param row 被操作行数据
         */
        proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
            if (!this.activate) return
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
        proxy.eventBus.on('onTableComSearch', (data: comSearchData[]) => {
            if (!this.activate) return
            this.table.filter!.search = data
            this.getIndex()
        })

        /**
         * 表格内的字段操作响应
         * @param value 修改后的值
         * @param row 被操作行数据
         * @param field 被操作字段名
         */
        proxy.eventBus.on('onTableFieldChange', (data: { value: any; row: TableRow; field: keyof TableRow; render: string }) => {
            if (!this.activate) return
            if (data.render == 'switch') {
                data.row.loading = true
                this.api
                    .postData('edit', { [this.table.pk!]: data.row[this.table.pk!], [data.field]: data.value })
                    .then(() => {
                        data.row.loading = false
                        data.row[data.field] = data.value
                    })
                    .catch(() => {
                        data.row.loading = false
                    })
            }
        })

        onUnmounted(() => {
            // 考虑到 keepalive 在 onMounted、onActivated 时注册事件；onUnmounted、onDeactivated 时注销事件，但并不是最方便的方案，且注销后在全局事件管理中本来就有留存
            // 干脆，不off掉事件，改用 this.activate 来决定事件是否执行
            this.activate = false
            this.runAfter('mount')
        })

        onActivated(() => {
            this.activate = true
        })

        onDeactivated(() => {
            this.activate = false
        })

        // 监听路由变化,响应通用搜索更新
        onBeforeRouteUpdate((to) => {
            this.initComSearch(to.query)
            this.getIndex()
        })
    }

    /**
     * 通用搜索初始化
     */
    initComSearch = (query: anyObj = {}) => {
        const form: anyObj = {}
        const field = this.table.column

        if (field.length <= 0) {
            return
        }

        for (const key in field) {
            if (field[key].operator === false) {
                continue
            }
            const prop = field[key].prop
            if (typeof field[key].operator == 'undefined') {
                field[key].operator = '='
            }
            if (prop) {
                if (field[key].operator == 'RANGE' || field[key].operator == 'NOT RANGE') {
                    form[prop] = ''
                    form[prop + '-start'] = ''
                    form[prop + '-end'] = ''
                } else if (field[key].operator == 'NULL' || field[key].operator == 'NOT NULL') {
                    form[prop] = false
                } else {
                    form[prop] = ''
                }

                // 初始化来自query中的默认值
                if (this.table.acceptQuery && typeof query[prop] != 'undefined') {
                    const queryProp = (query[prop] as string) ?? ''
                    if (field[key].operator == 'RANGE' || field[key].operator == 'NOT RANGE') {
                        const range = queryProp.split(',')
                        if (field[key].render == 'datetime') {
                            if (range && range.length >= 2) {
                                form[prop + '-default'] = [new Date(range[0]), new Date(range[1])]
                            }
                        } else {
                            form[prop + '-start'] = range[0] ?? ''
                            form[prop + '-end'] = range[1] ?? ''
                        }
                    } else if (field[key].operator == 'NULL' || field[key].operator == 'NOT NULL') {
                        form[prop] = queryProp ? true : false
                    } else if (field[key].render == 'datetime') {
                        form[prop + '-default'] = new Date(queryProp)
                    } else {
                        form[prop] = queryProp
                    }
                }

                this.comSearch.fieldData.set(prop, {
                    operator: field[key].operator,
                    render: field[key].render,
                    comSearchRender: field[key].comSearchRender,
                })
            }
        }

        // 接受query再搜索
        if (this.table.acceptQuery) {
            const comSearchData: comSearchData[] = []
            for (const key in query) {
                const fieldDataTemp = this.comSearch.fieldData.get(key)
                comSearchData.push({
                    field: key,
                    val: query[key] as string,
                    operator: fieldDataTemp.operator,
                    render: fieldDataTemp.render,
                })
            }
            this.table.filter!.search = comSearchData
        }

        this.comSearch.form = Object.assign(this.comSearch.form, form)
    }
}
