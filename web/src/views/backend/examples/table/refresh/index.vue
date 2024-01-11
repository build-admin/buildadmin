<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>刷新表格很简单，本示例花式刷新表格展示了其它表格使用细节，您可以按下<code>F12</code>查看更多信息</div>
                    <div>若单刷新表格，您只需使用<code>baTable.onTableHeaderAction('refresh', {})</code></div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.refresh.quick Search Fields') })"
        >
            <template #refreshSlot>
                <el-button @click="onRefresh" type="success" :loading="state.seconds != 3">在 {{ state.seconds }} 秒后刷新表格</el-button>
            </template>
        </TableHeader>

        <Table ref="tableRef"></Table>

        <div class="refresh">
            <el-button @click="onRefresh" type="success" v-blur :loading="state.seconds != 3">
                在 {{ state.seconds }} 秒后刷新当前表格，且服务端延迟返回
            </el-button>
        </div>

        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

defineOptions({
    name: 'examples/table/refresh',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Refresh/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.refresh.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.refresh.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                comSearchRender: 'slot',
                comSearchSlotName: 'refreshSlot',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        showComSearch: true,
    },
    {
        defaultItems: {},
    },
    {
        // 表头操作监听 - 操作前
        onTableHeaderAction({ event, data }) {
            if (event == 'refresh') console.info('%c-------表格数据开始刷新--------', 'color:blue')

            console.log('触发刷新时，传递的 data 为：', data)

            if (data?.sleep) {
                // 这个标记将被发送到服务端，并对本次请求休眠 3 秒再返回
                baTable.table.filter!.sleep = 1

                console.log('%c以要求服务端延迟返回', 'color:red')
            } else {
                console.log('%c未要求服务端延迟返回', 'color:green')
            }
        },
    },
    {
        // 表头操作监听 - 操作后
        onTableHeaderAction({ event }) {
            if (event == 'refresh') console.info('%c-------表格数据刷新完成，取消延迟标记（若有）！--------', 'color:blue')

            baTable.table.filter!.sleep = 0
        },
    }
)

const state = reactive({
    seconds: 3,
})

const onRefresh = () => {
    let timer = setInterval(() => {
        state.seconds--
        if (state.seconds == 0) {
            clearInterval(timer)

            // 刷新表格，仅此一行
            baTable.onTableHeaderAction('refresh', { sleep: true })

            state.seconds = 3
        }
    }, 1000)
}

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

<style scoped lang="scss">
.refresh {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 20px;
}
</style>
