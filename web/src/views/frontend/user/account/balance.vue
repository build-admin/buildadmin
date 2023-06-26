<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>{{ $t('user.account.balance.Balance change record') }}</span>
                    <span class="right-title">{{ $t('user.account.balance.Current balance') + ' ' + userInfo.money }}</span>
                </div>
            </template>
            <div v-loading="state.pageLoading" class="logs">
                <div class="log-item" v-for="(item, idx) in state.logs" :key="idx">
                    <div class="log-title">{{ item.memo }}</div>
                    <div v-if="item.money > 0" class="log-change-amount increase">{{ $t('Balance') + '：+' + item.money }}</div>
                    <div v-else class="log-change-amount reduce">{{ $t('Balance') + '：' + item.money }}</div>
                    <div class="log-after">{{ $t('user.account.balance.Balance after change') + '：' + item.after }}</div>
                    <div class="log-change-time">{{ $t('user.account.balance.Change time') + '：' + timeFormat(item.create_time) }}</div>
                </div>
            </div>
            <div v-if="state.total > 0" class="log-footer">
                <el-pagination
                    :currentPage="state.currentPage"
                    :page-size="state.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    background
                    :layout="memberCenter.state.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
                    :total="state.total"
                    @size-change="onTableSizeChange"
                    @current-change="onTableCurrentChange"
                ></el-pagination>
            </div>
            <el-empty v-else />
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { getBalanceLog } from '/@/api/frontend/user/index'
import { useMemberCenter } from '/@/stores/memberCenter'
import { timeFormat } from '/@/utils/common'
import { useUserInfo } from '/@/stores/userInfo'

const userInfo = useUserInfo()
const memberCenter = useMemberCenter()
const state: {
    logs: {
        memo: string
        create_time: number
        money: number
        after: number
    }[]
    currentPage: number
    total: number
    pageSize: number
    pageLoading: boolean
} = reactive({
    logs: [],
    currentPage: 1,
    total: 0,
    pageSize: 10,
    pageLoading: true,
})

const onTableSizeChange = (val: number) => {
    state.pageSize = val
    loadData()
}
const onTableCurrentChange = (val: number) => {
    state.currentPage = val
    loadData()
}

const loadData = () => {
    getBalanceLog(state.currentPage, state.pageSize).then((res) => {
        state.pageLoading = false
        state.logs = res.data.list
        state.total = res.data.total
    })
}

onMounted(() => {
    loadData()
})
</script>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.user-views-card :deep(.el-card__body) {
    padding-top: 0;
}
.right-title {
    color: var(--el-text-color-secondary);
}
.log-item {
    border-bottom: 1px solid var(--ba-bg-color);
    padding: 15px 0;
    div {
        padding: 4px 0;
    }
}
.log-title {
    font-size: var(--el-font-size-medium);
}
.log-change-amount.increase {
    color: var(--el-color-success);
}
.log-change-amount.reduce {
    color: var(--el-color-danger);
}
.log-after,
.log-change-time {
    font-size: var(--el-font-size-small);
    color: var(--el-text-color-secondary);
}
.log-footer {
    padding-top: 20px;
}
</style>
