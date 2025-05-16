<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('auth.rule.title') })"
        />

        <!-- 设置合适的 max-height 实现隐藏布局主体部分本身的滚动条，这样就可以监听表格的 @scroll 了 -->
        <!-- max-height = 100vh - (当前布局顶栏高度 + 表头栏高度 + 表格上边距 + 预留的底部下边距) -->
        <Table
            ref="tableRef"
            :max-height="`calc(-${adminLayoutHeaderBarHeight[config.layout.layoutMode as keyof typeof adminLayoutHeaderBarHeight] + 75 + 16}px + 100vh)`"
            :pagination="false"
            @expand-change="onExpandChange"
            @scroll="onScroll"
        />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { cloneDeep, debounce } from 'lodash-es'
import { nextTick, onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import { useConfig } from '/@/stores/config'
import baTableClass from '/@/utils/baTable'
import { adminLayoutHeaderBarHeight } from '/@/utils/layout'

defineOptions({
    name: 'auth/rule',
})

const { t } = useI18n()
const config = useConfig()
const tableRef = useTemplateRef('tableRef')

const baTable = new baTableClass(
    new baTableApi('/admin/auth.Rule/'),
    {
        expandAll: false,
        dblClickNotEditColumn: [undefined, 'keepalive', 'status'],
        column: [
            { type: 'selection', align: 'center' },
            { label: t('auth.rule.title'), prop: 'title', align: 'left', width: '200' },
            { label: t('auth.rule.Icon'), prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'fa fa-circle-o' },
            { label: t('auth.rule.name'), prop: 'name', align: 'center', showOverflowTooltip: true },
            {
                label: t('auth.rule.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
                replaceValue: { menu: t('auth.rule.type menu'), menu_dir: t('auth.rule.type menu_dir'), button: t('auth.rule.type button') },
            },
            { label: t('auth.rule.cache'), prop: 'keepalive', align: 'center', width: '80', render: 'switch' },
            { label: t('State'), prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: t('Update time'), prop: 'update_time', align: 'center', width: '160', render: 'datetime' },
            {
                label: t('Operate'),
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(),
            },
        ],
        dragSortLimitField: 'pid',
    },
    {
        defaultItems: {
            type: 'menu',
            menu_type: 'tab',
            extend: 'none',
            keepalive: 0,
            status: 1,
            icon: 'fa fa-circle-o',
        },
    }
)

/**
 * 内存缓存表格的一些状态数据，供数据刷新后恢复
 */
const sessionStateDefault = {
    expanded: [] as any[],
    scrollTop: 0,
    scrollLeft: 0,
    expandAll: false,
}
let sessionState = sessionStateDefault

/**
 * 记录表格行展开状态
 */
const onExpandChange = (row: any, expanded: boolean) => {
    if (expanded) {
        sessionState.expanded.push(row)
    } else {
        sessionState.expanded = sessionState.expanded.filter((item: any) => item.id !== row.id)
    }
}

/**
 * 记录表格滚动条位置
 */
const onScroll = debounce(({ scrollLeft, scrollTop }: { scrollLeft: number; scrollTop: number }) => {
    sessionState.scrollTop = scrollTop
    sessionState.scrollLeft = scrollLeft
}, 500)

/**
 * 记录表格行展开折叠状态
 */
const onUnfoldAll = (state: boolean) => {
    sessionState.expandAll = state
}

/**
 * 恢复已记录的表格状态
 */
const restoreState = () => {
    nextTick(() => {
        const sessionStateTemp = sessionState

        // 重置 sessionState 为默认值，恢复缓存的记录时将自动重设
        sessionState = cloneDeep(sessionStateDefault)

        for (const key in sessionStateTemp.expanded) {
            tableRef.value?.getRef()?.toggleRowExpansion(sessionStateTemp.expanded[key], true)
        }
        nextTick(() => {
            if (sessionStateTemp.scrollTop || sessionStateTemp.scrollLeft) {
                tableRef.value?.getRef()?.scrollTo({ top: sessionStateTemp.scrollTop || 0, left: sessionStateTemp.scrollLeft || 0 })
            }

            /**
             * expandAll 为 “是否默认展开所有行”
             * 此处表格数据已渲染，仅做顶部按钮状态标记用，不会实际上的执行展开折叠操作
             * 展开全部行之后，再只对某一行进行折叠时，expandAll 不会改变，所以此处并不根据 expandAll 值执行折叠展开所有行的操作
             */
            baTable.table.expandAll = sessionStateTemp.expandAll
            onUnfoldAll(sessionStateTemp.expandAll)
        })
    })
}

// 获取数据前钩子
baTable.before.getData = () => {
    baTable.table.expandAll = baTable.table.filter?.quickSearch ? true : false
}

// 获取到编辑行数据后的钩子
baTable.after.getEditData = () => {
    if (baTable.form.items && !baTable.form.items.icon) {
        baTable.form.items.icon = 'fa fa-circle-o'
    }
}

// 表格顶部按钮事件触发后的钩子
baTable.after.onTableHeaderAction = ({ event, data }) => {
    if (event == 'unfold') {
        onUnfoldAll(data.unfold)
    }
}

// 获取到表格数据后的钩子
baTable.after.getData = () => {
    restoreState()
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()?.then(() => {
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss">
.default-main {
    margin-bottom: 0;
}
</style>
