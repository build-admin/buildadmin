import { reactive, onMounted, onUnmounted } from 'vue'
import { index, edit, del, postData } from '/@/api/backend/routine/Attachment'
import { adminBuildSuffixSvgUrl } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import { getArrayKey } from '/@/utils/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'

interface Table {
    // 主键字段
    pk?: string
    // 数据源
    data?: TableRow[]
    // 路由remark
    remark?: string | null
    // 表格加载状态
    loading?: boolean
    // 选中项
    selection?: TableRow[]
    // 不需要'双击编辑'的字段
    dblClickNotEditColumn?: (string | undefined)[]
    // 列数据
    column: TableColumn[]
    // 数据总量
    total?: number
    // 字段搜索,快速搜索,分页等数据
    filter?: anyObj
    // 默认排序字段和排序方式:asc=顺序,desc=倒序
    defaultOrder?: { prop: string; order: string }
}

export default class baTable {
    public table = reactive({
        pk: 'id',
        data: [],
        remark: null,
        loading: false,
        selection: [],
        dblClickNotEditColumn: [undefined],
        column: [],
        total: 0,
        filter: {},
    })

    constructor(table: Table) {
        this.table = Object.assign(this.table, table)
    }
}
