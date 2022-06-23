<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>{{ $t('user.user.Account information') }}</span>
                    <el-button @click="router.push({ name: 'account/profile' })" type="info" v-blur plain>{{
                        $t('user.user.personal data')
                    }}</el-button>
                </div>
            </template>
            <div class="overview-userinfo">
                <div class="user-avatar">
                    <img :src="userInfo.avatar" alt="" />
                    <div class="user-avatar-icons">
                        <div class="avatar-icon-item">
                            <el-tooltip
                                effect="light"
                                placement="right"
                                :content="(userInfo.mobile ? $t('user.user.Filled in') : $t('user.user.Not filled in')) + $t('user.user.mobile')"
                            >
                                <Icon name="fa fa-tablet" size="16" :color="userInfo.mobile ? 'var(--color-primary)' : 'var(--color-info)'" />
                            </el-tooltip>
                        </div>
                        <div class="avatar-icon-item">
                            <el-tooltip
                                effect="light"
                                placement="right"
                                :content="(userInfo.email ? $t('user.user.Filled in') : $t('user.user.Not filled in')) + $t('user.user.mailbox')"
                            >
                                <Icon name="fa fa-envelope-square" size="14" :color="userInfo.email ? 'var(--color-primary)' : 'var(--color-info)'" />
                            </el-tooltip>
                        </div>
                    </div>
                </div>
                <div class="user-data">
                    <div class="welcome-words">{{ userInfo.nickname + $t('utils.comma') + getGreet() }}</div>
                    <el-row class="data-item">
                        <el-col :span="4">{{ $t('user.user.integral') }}</el-col>
                        <el-col :span="8">
                            <el-link @click="router.push({ name: 'account/integral' })" type="primary">{{ userInfo.score }}</el-link>
                        </el-col>
                        <el-col :span="4">{{ $t('user.user.balance') }}</el-col>
                        <el-col :span="8">
                            <el-link @click="router.push({ name: 'account/balance' })" type="primary">{{ userInfo.money }}</el-link>
                        </el-col>
                    </el-row>
                    <el-row class="data-item">
                        <el-col :span="4">{{ $t('user.user.Last login') }}</el-col>
                        <el-col :span="8">{{ timeFormat(userInfo.lastlogintime) }}</el-col>
                        <el-col :span="4">{{ $t('user.user.Last login IP') }}</el-col>
                        <el-col :span="8">{{ userInfo.lastloginip }}</el-col>
                    </el-row>
                </div>
            </div>
        </el-card>
        <el-card class="user-views-card" shadow="hover" :header="$t('user.user.Growth statistics')">
            <div class="account-growth" ref="accountGrowthChartRef"></div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, nextTick, onActivated, onMounted, onBeforeMount } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { timeFormat } from '/@/components/table'
import { useRouter } from 'vue-router'
import * as echarts from 'echarts'
import { useI18n } from 'vue-i18n'
import { getGreet } from '/@/utils/common'
import { overview } from '/@/api/frontend/user/index'

const { t } = useI18n()
const router = useRouter()
const userInfo = useUserInfo()
const accountGrowthChartRef = ref<HTMLElement>()

const state: {
    days: string[]
    score: number[]
    money: number[]
    charts: any[]
} = reactive({
    days: [],
    score: [],
    money: [],
    charts: [],
})

const initUserGrowthChart = () => {
    const userGrowthChart = echarts.init(accountGrowthChartRef.value!)
    const option = {
        grid: {
            top: 10,
            right: 0,
            bottom: 20,
            left: 50,
        },
        xAxis: {
            data: state.days,
        },
        yAxis: {},
        legend: {
            data: [t('user.user.integral'), t('user.user.balance')],
        },
        series: [
            {
                name: t('user.user.integral'),
                data: state.score,
                type: 'line',
                smooth: true,
                show: false,
                color: '#f56c6c',
                emphasis: {
                    label: {
                        show: true,
                    },
                },
                areaStyle: {},
            },
            {
                name: t('user.user.balance'),
                data: state.money,
                type: 'line',
                smooth: true,
                show: false,
                color: '#409eff',
                emphasis: {
                    label: {
                        show: true,
                    },
                },
                areaStyle: {
                    opacity: 0.4,
                },
            },
        ],
    }
    userGrowthChart.setOption(option)
    state.charts.push(userGrowthChart)
}

const echartsResize = () => {
    nextTick(() => {
        for (const key in state.charts) {
            state.charts[key].resize()
        }
    })
}

onActivated(() => {
    echartsResize()
})

onMounted(() => {
    overview().then((res) => {
        state.days = res.data.days
        state.score = res.data.score
        state.money = res.data.money
        initUserGrowthChart()
    })
    window.addEventListener('resize', echartsResize)
})

onBeforeMount(() => {
    for (const key in state.charts) {
        state.charts[key].dispose()
    }
    window.removeEventListener('resize', echartsResize)
})
</script>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.overview-userinfo {
    display: flex;
    width: 100%;
    background-color: #fff;
    overflow: hidden;
    .user-avatar {
        width: 100px;
        padding: 0 20px;
        margin: 20px 0;
        border-right: 1px solid var(--color-sub-2);
        img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
    }
    .user-avatar-icons {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 4px;
    }
    .avatar-icon-item {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3px;
        border: 1px solid var(--color-sub-2);
        border-radius: 50%;
        margin: 3px;
        cursor: pointer;
        &:hover {
            border: 1px solid var(--color-primary);
        }
        .icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
        }
    }
    .user-data {
        padding: 0 20px;
        margin: 20px 0;
        width: calc(100% - 100px);
    }
    .welcome-words {
        color: var(--color-text-primary);
        font-size: var(--el-font-size-medium);
        padding: 20px 0;
    }
    .data-item {
        font-size: var(--el-font-size-base);
        padding: 3px 0;
    }
}
.account-growth {
    width: 100%;
    height: 300px;
}
</style>
