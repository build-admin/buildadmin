<template>
    <div class="default-main">
        <div class="ba-table-box">
            <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'edit', 'comSearch', 'quickSearch', 'columnDisplay']"
                :quick-search-placeholder="t('quick Search Placeholder', { fields: t('routine.attachment.Original name') })"
            >
                <el-popconfirm
                    v-if="auth('del')"
                    @confirm="baTable.onTableHeaderAction('delete', {})"
                    :confirm-button-text="t('delete')"
                    :cancel-button-text="t('Cancel')"
                    confirmButtonType="danger"
                    :title="t('routine.attachment.Files and records will be deleted at the same time Are you sure?')"
                    :disabled="baTable.table.selection!.length > 0 ? false : true"
                >
                    <template #reference>
                        <div class="mlr-12">
                            <el-tooltip :content="t('Delete selected row')" placement="top">
                                <el-button
                                    v-blur
                                    :disabled="baTable.table.selection!.length > 0 ? false : true"
                                    class="table-header-operate"
                                    type="danger"
                                >
                                    <Icon color="#ffffff" name="fa fa-trash" />
                                    <span class="table-header-operate-text">{{ t('delete') }}</span>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>
            </TableHeader>

            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table ref="tableRef" @action="baTable.onTableAction" />

            <!-- 编辑和新增表单 -->
            <PopupForm />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import baTableClass from '/@/utils/baTable'
import { routineAttachment } from '/@/api/controllerUrls'
import { defaultOptButtons } from '/@/components/table'
import { previewRenderFormatter } from './index'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import { auth } from '/@/utils/common'

const { t } = useI18n()
const tableRef = ref()

const optBtn = defaultOptButtons(['edit', 'delete'])
optBtn[1].popconfirm.title = t('routine.attachment.Files and records will be deleted at the same time Are you sure?')

const baTable = new baTableClass(new baTableApi(routineAttachment), {
    column: [
        { type: 'selection', align: 'center', operator: false },
        { label: t('id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
        { label: t('routine.attachment.Breakdown'), prop: 'topic', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
        {
            label: t('routine.attachment.Upload administrator'),
            prop: 'admin.nickname',
            align: 'center',
            operator: 'LIKE',
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('routine.attachment.Upload user'),
            prop: 'user.nickname',
            align: 'center',
            operator: 'LIKE',
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('routine.attachment.size'),
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
        {
            label: t('routine.attachment.type'),
            prop: 'mimetype',
            align: 'center',
            operator: 'LIKE',
            'show-overflow-tooltip': true,
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('routine.attachment.preview'),
            prop: 'suffix',
            align: 'center',
            renderFormatter: previewRenderFormatter,
            render: 'image',
            operator: false,
        },
        {
            label: t('routine.attachment.Upload (Reference) times'),
            prop: 'quote',
            align: 'center',
            width: 150,
            operator: 'RANGE',
            sortable: 'custom',
        },
        {
            label: t('routine.attachment.Original name'),
            prop: 'name',
            align: 'center',
            'show-overflow-tooltip': true,
            operator: 'LIKE',
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('routine.attachment.Storage mode'),
            prop: 'storage',
            align: 'center',
            width: 100,
            operator: 'LIKE',
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('routine.attachment.Last upload time'),
            prop: 'lastuploadtime',
            align: 'center',
            render: 'datetime',
            operator: 'RANGE',
            width: 160,
            sortable: 'custom',
        },
        {
            label: t('operate'),
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: optBtn,
            operator: false,
        },
    ],
    defaultOrder: { prop: 'lastuploadtime', order: 'desc' },
})

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'routine/attachment',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
.table-header-operate-text {
    margin-left: 6px;
}
</style>
