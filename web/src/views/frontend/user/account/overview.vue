<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>{{ $t('user.account.overview.Account information') }}</span>
                    <el-button @click="router.push({ name: 'account/profile' })" type="info" v-blur plain>
                        {{ $t('user.account.overview.profile') }}
                    </el-button>
                </div>
            </template>
            <div class="overview-userinfo">
                <div class="user-avatar">
                    <img :src="fullUrl(userInfo.avatar)" alt="" />
                    <div class="user-avatar-icons">
                        <div @click="router.push({ name: 'account/profile' })" class="avatar-icon-item">
                            <el-tooltip
                                effect="light"
                                placement="right"
                                :content="
                                    (userInfo.mobile ? $t('user.account.overview.Filled in') : $t('user.account.overview.Not filled in')) +
                                    $t('user.account.overview.mobile')
                                "
                            >
                                <Icon
                                    name="fa fa-tablet"
                                    size="16"
                                    :color="userInfo.mobile ? 'var(--el-color-primary)' : 'var(--el-text-color-secondary)'"
                                />
                            </el-tooltip>
                        </div>
                        <div @click="router.push({ name: 'account/profile' })" class="avatar-icon-item">
                            <el-tooltip
                                effect="light"
                                placement="right"
                                :content="
                                    (userInfo.email ? $t('user.account.overview.Filled in') : $t('user.account.overview.Not filled in')) +
                                    $t('user.account.overview.email')
                                "
                            >
                                <Icon
                                    name="fa fa-envelope-square"
                                    size="14"
                                    :color="userInfo.email ? 'var(--el-color-primary)' : 'var(--el-text-color-secondary)'"
                                />
                            </el-tooltip>
                        </div>
                    </div>
                </div>
                <div class="user-data">
                    <div class="welcome-words">{{ userInfo.nickname + $t('utils.comma') + getGreet() }}</div>
                    <el-row class="data-item">
                        <el-col :span="4">{{ $t('Integral') }}</el-col>
                        <el-col :span="8">
                            <el-link @click="router.push({ name: 'account/integral' })" type="primary">{{ userInfo.score }}</el-link>
                        </el-col>
                        <el-col :span="4">{{ $t('Balance') }}</el-col>
                        <el-col :span="8">
                            <el-link @click="router.push({ name: 'account/balance' })" type="primary">{{ userInfo.money }}</el-link>
                        </el-col>
                    </el-row>
                    <el-row class="data-item">
                        <el-col class="lastlogin title" :span="4">{{ $t('user.account.overview.Last login') }}</el-col>
                        <el-col class="lastlogin value" :span="8">{{ timeFormat(userInfo.last_login_time) }}</el-col>
                        <el-col class="lastip" :span="4">{{ $t('user.account.overview.Last login IP') }}</el-col>
                        <el-col class="lastip" :span="8">{{ userInfo.last_login_ip }}</el-col>
                    </el-row>
                </div>
            </div>
        </el-card>
        <el-card class="user-views-card" shadow="hover" :header="$t('user.account.overview.Growth statistics')">
            <div class="account-growth" ref="accountGrowthChartRef"></div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { reactive, nextTick, onActivated, onMounted, onBeforeMount, useTemplateRef } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useRouter } from 'vue-router'
import * as echarts from 'echarts'
import { useI18n } from 'vue-i18n'
import { fullUrl, getGreet, timeFormat } from '/@/utils/common'
import { overview } from '/@/api/frontend/user/index'
import { useEventListener } from '@vueuse/core'

const { t } = useI18n()
const router = useRouter()
const userInfo = useUserInfo()
const accountGrowthChartRef = useTemplateRef('accountGrowthChartRef')

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
            data: [t('Integral'), t('Balance')],
        },
        series: [
            {
                name: t('Integral'),
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
                name: t('Balance'),
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
    useEventListener(window, 'resize', echartsResize)
})

onBeforeMount(() => {
    for (const key in state.charts) {
        state.charts[key].dispose()
    }
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
    background-color: var(--ba-bg-color-overlay);
    overflow: hidden;
    .user-avatar {
        width: 100px;
        padding: 0 20px;
        margin: 20px 0;
        border-right: 1px solid var(--el-border-color-light);
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
        border: 1px solid var(--el-border-color-light);
        border-radius: 50%;
        margin: 3px;
        cursor: pointer;
        &:hover {
            border: 1px solid var(--el-color-primary);
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
        color: var(--el-text-color-primary);
        font-size: var(--el-font-size-medium);
        padding: 20px 0;
    }
    .data-item {
        display: flex;
        align-items: center;
        font-size: var(--el-font-size-base);
        padding: 3px 0;
    }
}
.account-growth {
    width: 100%;
    height: 300px;
}
@media screen and (max-width: 992px) {
    .user-data {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .overview-userinfo .welcome-words {
        padding-top: 0;
    }
    .user-avatar {
        display: none;
    }
}
@media screen and (max-width: 1280px) and (min-width: 992px) {
    .lastip {
        display: none;
    }
    .lastlogin.title {
        width: 42%;
        max-width: 42%;
        flex: 0 0 42%;
    }
    .lastlogin.value {
        width: 58%;
        max-width: 58%;
        flex: 0 0 58%;
    }
}
@media screen and (max-width: 460px) {
    .lastip {
        display: none;
    }
    .lastlogin.title {
        width: 42%;
        max-width: 42%;
        flex: 0 0 42%;
    }
    .lastlogin.value {
        width: 58%;
        max-width: 58%;
        flex: 0 0 58%;
    }
}
</style>
