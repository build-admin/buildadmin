<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="
                t('Quick search placeholder', { fields: t('user.moneyLog.User name') + '/' + t('user.moneyLog.User nickname') })
            "
        >
            <el-button v-if="!isEmpty(state.userInfo)" v-blur class="table-header-operate">
                <span class="table-header-operate-text">{{
                    state.userInfo.username + '(ID:' + state.userInfo.id + ') ' + t('user.moneyLog.balance') + ':' + state.userInfo.money
                }}</span>
            </el-button>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { isEmpty, parseInt } from 'lodash-es'
import { ref, provide, reactive, watch } from 'vue'
import baTableClass from '/@/utils/baTable'
import { userMoneyLog } from '/@/api/controllerUrls'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { baTableApi } from '/@/api/common'
import { useRoute } from 'vue-router'
import { add } from '/@/api/backend/user/moneyLog'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const tableRef = ref()
const route = useRoute()
const defalutUser = (route.query.user_id ?? '') as string
const state: {
    userInfo: anyObj
} = reactive({
    userInfo: {},
})

const baTable = new baTableClass(
    new baTableApi(userMoneyLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('Id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
            { label: t('user.moneyLog.User ID'), prop: 'user_id', align: 'center', operator: '=', width: 70 },
            { label: t('user.moneyLog.User name'), prop: 'user.username', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('user.moneyLog.User nickname'),
                prop: 'user.nickname',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            { label: t('user.moneyLog.Change balance'), prop: 'money', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('user.moneyLog.Before change'), prop: 'before', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('user.moneyLog.After change'), prop: 'after', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('user.moneyLog.remarks'),
                prop: 'memo',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                'show-overflow-tooltip': true,
            },
            { label: t('Create time'), prop: 'create_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
        ],
        dblClickNotEditColumn: ['all'],
    },
    {
        defaultItems: {
            user_id: defalutUser,
            memo: '',
        },
    },
    {},
    {
        onSubmit: () => {
            getUserInfo(baTable.comSearch.form.user_id)
        },
    }
)

baTable.mount()
baTable.getIndex()

provide('baTable', baTable)

const getUserInfo = (userId: string) => {
    if (userId && parseInt(userId) > 0) {
        add(userId).then((res) => {
            state.userInfo = res.data.user
        })
    } else {
        state.userInfo = {}
    }
}

getUserInfo(baTable.comSearch.form.user_id)

watch(
    () => baTable.comSearch.form.user_id,
    (newVal) => {
        baTable.form.defaultItems!.user_id = newVal
        getUserInfo(newVal)
    }
)
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'user/moneyLog',
})
</script>

<style scoped lang="scss"></style>
