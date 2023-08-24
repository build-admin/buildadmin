<template>
    <div class="default-main">
        <div class="ba-table-box">
            <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'edit', 'comSearch', 'quickSearch', 'columnDisplay']"
                :quick-search-placeholder="t('Quick search placeholder', { fields: t('utils.Original name') })"
            >
                <el-popconfirm
                    v-if="auth('del')"
                    @confirm="baTable.onTableHeaderAction('delete', {})"
                    :confirm-button-text="t('Delete')"
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
                                    <span class="table-header-operate-text">{{ t('Delete') }}</span>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>
            </TableHeader>

            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table ref="tableRef" />

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
import { defaultOptButtons } from '/@/components/table'
import { previewRenderFormatter } from './index'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import { auth } from '/@/utils/common'

defineOptions({
    name: 'routine/attachment',
})

const { t } = useI18n()
const tableRef = ref()

const optBtn = defaultOptButtons(['edit', 'delete'])
optBtn[1].popconfirm!.title = t('routine.attachment.Files and records will be deleted at the same time Are you sure?')

const baTable = new baTableClass(new baTableApi('/admin/routine.Attachment/'), {
    column: [
        { type: 'selection', align: 'center', operator: false },
        { label: t('Id'), prop: 'id', align: 'center', operator: '=', operatorPlaceholder: t('Id'), width: 70 },
        { label: t('utils.Breakdown'), prop: 'topic', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
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
            label: t('utils.size'),
            prop: 'size',
            align: 'center',
            formatter: (row: TableRow, column: TableColumn, cellValue: string) => {
                const size = parseFloat(cellValue)
                const i = Math.floor(Math.log(size) / Math.log(1024))
                return (size / Math.pow(1024, i)).toFixed(i < 1 ? 0 : 2) + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i]
            },
            operator: 'RANGE',
            sortable: 'custom',
            operatorPlaceholder: 'bytes',
        },
        {
            label: t('utils.type'),
            prop: 'mimetype',
            align: 'center',
            operator: 'LIKE',
            showOverflowTooltip: true,
            operatorPlaceholder: t('Fuzzy query'),
        },
        {
            label: t('utils.preview'),
            prop: 'suffix',
            align: 'center',
            renderFormatter: previewRenderFormatter,
            render: 'image',
            operator: false,
        },
        {
            label: t('utils.Upload (Reference) times'),
            prop: 'quote',
            align: 'center',
            width: 150,
            operator: 'RANGE',
            sortable: 'custom',
        },
        {
            label: t('utils.Original name'),
            prop: 'name',
            align: 'center',
            showOverflowTooltip: true,
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
            label: t('utils.Last upload time'),
            prop: 'last_upload_time',
            align: 'center',
            render: 'datetime',
            operator: 'RANGE',
            width: 160,
            sortable: 'custom',
        },
        {
            label: t('Operate'),
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: optBtn,
            operator: false,
        },
    ],
    defaultOrder: { prop: 'last_upload_time', order: 'desc' },
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

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
.table-header-operate-text {
    margin-left: 6px;
}
</style>
