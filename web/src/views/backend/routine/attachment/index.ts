import { reactive, onMounted, onUnmounted } from 'vue'
import { index, edit, del, postData } from '/@/api/backend/routine/Attachment'
import { adminBuildSuffixSvgUrl } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import { getArrayKey } from '/@/utils/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'

/**
 * 表格和表单中文件预览图的生成
 */
export const previewRenderFormatter = (row: TableRow, column: TableColumn, cellValue: string) => {
    let imgSuffix = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']
    if (imgSuffix.includes(cellValue)) {
        return row.full_url
    }
    return adminBuildSuffixSvgUrl(cellValue)
}

/* 表格状态-s */
const opButtons = defaultOptButtons()
const sortButtonsKey = getArrayKey(opButtons, 'name', 'weigh-sort')
opButtons.splice(sortButtonsKey, 1)
export const table: {
    // 主键字段
    pk: string
    // 数据源
    data: TableRow[]
    // 路由remark
    remark: string | null
    // 表格加载状态
    loading: boolean
    // 选中项
    selection: TableRow[]
    // 不需要'双击编辑'的字段
    dblClickNotEditColumn: (string | undefined)[]
    // 列数据
    column: TableColumn[]
    // 数据总量
    total: number
    // 字段搜索,快速搜索,分页等数据
    filter: anyObj
    // 默认排序字段和排序方式:asc=顺序,desc=倒序
    defaultOrder: { prop: string; order: string }
} = reactive({
    pk: 'id',
    data: [],
    remark: null,
    loading: false,
    selection: [],
    dblClickNotEditColumn: [undefined],
    column: [
        { type: 'selection', align: 'center', operator: false },
        { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
        { label: '细目', prop: 'topic', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '上传管理员', prop: 'admin.nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        {
            label: '大小',
            prop: 'size',
            align: 'center',
            formatter: (row: TableRow, column: TableColumn, cellValue: string, index: number) => {
                var size = parseFloat(cellValue)
                var i = Math.floor(Math.log(size) / Math.log(1024))
                return parseInt((size / Math.pow(1024, i)).toFixed(i < 2 ? 0 : 2)) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i]
            },
            operator: 'RANGE',
            sortable: 'custom',
            operatorPlaceholder: 'bytes',
        },
        { label: '类型', prop: 'mimetype', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        {
            label: '预览',
            prop: 'suffix',
            align: 'center',
            renderFormatter: previewRenderFormatter,
            render: 'image',
            operator: false,
        },
        { label: '上传(引用)次数', prop: 'quote', align: 'center', width: 150, operator: 'RANGE', sortable: 'custom' },
        { label: '原始名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true, operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '存储方式', prop: 'storage', align: 'center', width: 100, operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '最后上传时间', prop: 'lastuploadtime', align: 'center', render: 'datetime', operator: 'RANGE', width: 160, sortable: 'custom' },
        {
            label: '操作',
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: opButtons,
            operator: false,
        },
    ],
    total: 0,
    filter: {},
    defaultOrder: { prop: 'lastuploadtime', order: 'desc' },
})
/* 表格状态-e */

/* 表单状态-s */
export const form: {
    labelWidth: number
    operate: string
    operateIds: string[]
    items: anyObj
    submitLoading: boolean
    defaultItems: anyObj
} = reactive({
    // 表单label宽度
    labelWidth: 160,
    // 当前操作:add=添加,edit=编辑
    operate: '',
    // 被操作数据ID,支持批量编辑:add=[0],edit=[1,2,n]
    operateIds: [],
    // 表单数据
    items: {},
    submitLoading: false,
    // 默认表单数据(添加)
    defaultItems: {},
})
/* 表单状态-e */

/* API请求方法-s */
export const getIndex = () => {
    table.loading = true
    return index(table.filter)
        .then((res) => {
            table.data = res.data.list
            table.total = res.data.total
            table.remark = res.data.remark
            table.loading = false
        })
        .catch(() => {
            table.loading = false
        })
}
// 发送删除请求
const postDel = (ids: string[]) => {
    del(ids).then((res) => {
        onTableHeaderAction('refresh', {})
    })
}
// 请求编辑数据
const requestEdit = (id: string) => {
    form.items = {}
    edit({
        id: id,
    }).then((res) => {
        form.items = res.data.row
    })
}
/* API请求方法-e */

/**
 * 双击表格
 */
export const onTableDblclick = (row: TableRow, column: any) => {
    if (table.dblClickNotEditColumn.indexOf(column.property) === -1) {
        toggleForm('edit', [row[table.pk]])
    }
}

/**
 * 打开表单
 * @param operate 操作:add=添加,edit=编辑
 * @param operateIds 被操作项的数组:add=[],edit=[1,2,...]
 */
export const toggleForm = (operate: string = '', operateIds: string[] = []) => {
    if (operate == 'edit') {
        if (!operateIds.length) {
            return false
        }
        requestEdit(operateIds[0])
    } else if (operate == 'add') {
        form.items = form.defaultItems
    }
    form.operate = operate
    form.operateIds = operateIds
}

/**
 * 提交表单
 */
export const onSubmit = () => {
    form.submitLoading = true
    postData(form.operate, form.items)
        .then((res) => {
            onTableHeaderAction('refresh', {})
            form.submitLoading = false
            form.operateIds.shift()
            if (form.operateIds.length > 0) {
                toggleForm('edit', form.operateIds)
            } else {
                toggleForm()
            }
        })
        .catch((err) => {
            form.submitLoading = false
        })
}

/* 获取表格选择项的id数组 */
const getSelectionIds = () => {
    let ids: string[] = []
    for (const key in table.selection) {
        ids.push(table.selection[key][table.pk])
    }
    return ids
}

/**
 * 表格顶栏按钮事件响应
 * @param event 事件:refresh=刷新,edit=编辑,delete=删除,quick-search=快速查询
 * @param data 携带数据
 */
export const onTableHeaderAction = (event: string, data: anyObj) => {
    const actionFun = new Map([
        [
            'refresh',
            () => {
                table.data = []
                getIndex()
            },
        ],
        [
            'edit',
            () => {
                toggleForm('edit', getSelectionIds())
            },
        ],
        [
            'delete',
            () => {
                postDel(getSelectionIds())
            },
        ],
        [
            'quick-search',
            () => {
                table.filter.quick_search = data.keyword
                getIndex()
            },
        ],
        [
            'change-show-column',
            () => {
                let columnKey = getArrayKey(table.column, 'prop', data.field)
                table.column[columnKey].show = data.value
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

/**
 * 表格内的事件响应
 * @param event 事件:selection-change=选中项改变
 * @param data 携带数据
 */
export const onTableAction = (event: string, data: anyObj) => {
    const actionFun = new Map([
        [
            'selection-change',
            () => {
                table.selection = data as TableRow[]
            },
        ],
        [
            'page-size-change',
            () => {
                table.filter.limit = data.size
                getIndex()
            },
        ],
        [
            'current-page-change',
            () => {
                table.filter.page = data.page
                getIndex()
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
                if (newOrder != table.filter.order) {
                    table.filter.order = newOrder
                    getIndex()
                }
            },
        ],
    ])

    let action = actionFun.get(event) || actionFun.get('default')
    return action!.call(this)
}

export const mount = () => {
    const { proxy } = useCurrentInstance()
    onMounted(() => {
        /**
         * 表格内的按钮响应
         * @param name 按钮name
         * @param row 被操作行数据
         */
        proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
            if (data.name == 'edit') {
                toggleForm('edit', [data.row[table.pk]])
            } else if (data.name == 'delete') {
                postDel([data.row[table.pk]])
            }
        })

        /**
         * 通用搜索响应
         * @param comSearchData 通用搜索数据
         */
        proxy.eventBus.on('onTableComSearch', (data: comSearchData) => {
            table.filter.search = data
            getIndex()
        })
    })

    onUnmounted(() => {
        proxy.eventBus.off('onTableComSearch')
        proxy.eventBus.off('onTableButtonClick')
    })
}
