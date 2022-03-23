import useCurrentInstance from '/@/utils/useCurrentInstance'
<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        <!-- 表格顶部菜单 -->
        <TableHeader
            :field="baTable.table.column"
            :buttons="['refresh', 'delete', 'comSearch']"
            :enable-batch-opt="baTable.table.selection!.length > 0 ? true : false"
            :quick-search-placeholder="'通过标题模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table
            ref="tableRef"
            :data="baTable.table.data"
            :field="baTable.table.column"
            :row-key="baTable.table.pk"
            :total="baTable.table.total"
            :loading="baTable.table.loading"
            @action="baTable.onTableAction"
            @row-dblclick="baTable.onTableDblclick"
        />

        <!-- 查看详情 -->
        <el-dialog
            custom-class="ba-operate-dialog"
            :close-on-click-modal="false"
            :model-value="state.showInfoModel"
            @close="state.showInfoModel = false"
        >
            <template #title>
                <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">查看详情</div>
            </template>
            <el-table row-key="label" :data="state.info" stripe class="w100">
                <el-table-column prop="label" label="项目" width="180" />
                <el-table-column prop="value" label="值" />
            </el-table>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import _ from 'lodash'
import { ref, reactive, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { authAdminLog } from '/@/api/controllerUrls'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { useI18n } from 'vue-i18n'
import { timeFormat } from '/@/components/table'

interface Info {
    label: string
    value: string
    children?: Info[]
}

const state: {
    showInfoModel: boolean
    info: Info[]
} = reactive({
    showInfoModel: false,
    info: [],
})

const { t } = useI18n()

let optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'info',
        title: 'info',
        text: '',
        type: 'primary',
        icon: 'fa fa-search-plus',
        class: 'table-row-edit',
        disabledTip: false,
    },
]

optButtons = _.concat(optButtons, defaultOptButtons(['delete']))

const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi(authAdminLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('auth/adminLog.id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
            { label: t('auth/adminLog.admin_id'), prop: 'admin_id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
            { label: t('auth/adminLog.username'), prop: 'username', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 160 },
            { label: t('auth/adminLog.title'), prop: 'title', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            {
                show: false,
                label: t('auth/adminLog.data'),
                prop: 'data',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: '模糊查询',
                'show-overflow-tooltip': true,
            },
            {
                label: t('auth/adminLog.url'),
                prop: 'url',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: '模糊查询',
                'show-overflow-tooltip': true,
                render: 'url'
            },
            { label: t('auth/adminLog.ip'), prop: 'ip', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', render: 'tags' },
            {
                label: t('auth/adminLog.useragent'),
                prop: 'useragent',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: '模糊查询',
                'show-overflow-tooltip': true,
            },
            {
                label: t('auth/adminLog.createtime'),
                prop: 'createtime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            {
                label: '操作',
                align: 'center',
                width: '100',
                render: 'buttons',
                buttons: optButtons,
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {},
    {},
    {
        onTableDblclick: ({ row }: { row: TableRow }) => {
            infoButtonClick(row)
        },
    }
)

baTable.mount()
baTable.getIndex()

const buildChildrens = (data: any): Info[] => {
    if (typeof data == 'object') {
        let childrens = []
        for (const key in data) {
            childrens.push({
                label: key,
                value: data[key],
                children: buildChildrens(data[key]),
            })
        }
        return childrens
    } else {
        return []
    }
}

const infoButtonClick = (row: TableRow) => {
    if (!row) return
    let rowClone = _.cloneDeep(row)

    if (rowClone.data) rowClone.data = JSON.parse(rowClone.data)
    let info = []
    for (const key in rowClone) {
        if (key == 'createtime') {
            rowClone[key] = timeFormat(rowClone[key])
        }

        info.push({
            label: t('auth/adminLog.' + key),
            value: rowClone[key],
            children: key == 'data' ? buildChildrens(rowClone[key]) : [],
        })
    }
    state.info = info
    state.showInfoModel = true
}

onMounted(() => {
    const { proxy } = useCurrentInstance()
    /**
     * 表格内的按钮响应
     * @param name 按钮name
     * @param row 被操作行数据
     */
    proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
        if (data.name == 'info') {
            infoButtonClick(data.row)
        }
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'auth/adminLog',
})
</script>

<style scoped lang="scss">
:deep(.ba-operate-dialog) .el-dialog__body {
    padding: 0 10px;
}
</style>
