<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>多行或多列共用一个数据时，可以合并行或列。</div>
                    <div>
                        以<code>id</code>来识别行，我们将<code>1、2</code>行的<code>用户名</code>合并了，示例数据中，这两行的用户名，都是<code
                            >user</code
                        >
                    </div>
                    <div>
                        同时，我们将<code>2、3</code>行的<code>城市、详细地址、邮编</code>这三列进行了合并，示例数据中，这两行的<code>详细地址</code>已经包含了城市和邮编
                    </div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.span.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/2) -->
        <Table :span-method="spanMethod" ref="tableRef"></Table>

        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

defineOptions({
    name: 'examples/table/span',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * 示例核心代码(2/2)
 */
const spanMethod = ({ rowIndex, columnIndex }: { row: TableRow; column: TableColumn; rowIndex: number; columnIndex: number }) => {
    // 前两行 - 合并列
    if (rowIndex == 0 || rowIndex == 1) {
        if (columnIndex === 4 || columnIndex === 6) {
            // 第4、6列不显示（城市和邮编）
            return [0, 0]
        } else if (columnIndex === 5) {
            // 第5列显示，并且合并3个col（详细地址）
            return [1, 3]
        }
    }

    // 后两行 - 合并行
    if ((rowIndex == 1 || rowIndex == 2) && columnIndex == 2) {
        if (rowIndex == 1) {
            return [2, 1]
        } else {
            return [0, 0]
        }
    }
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Span/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.span.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.span.user__username'),
                prop: 'user.username',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            { label: t('examples.table.span.date'), prop: 'date', align: 'center', operator: 'eq', sortable: 'custom' },
            { label: t('examples.table.span.city'), prop: 'city', align: 'center', operator: false },
            {
                label: t('examples.table.span.address'),
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.span.code'),
                prop: 'code',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.span.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { date: null },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
