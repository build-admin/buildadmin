<template>
    <div>
        <el-dialog v-model="state.dialog.buy" class="buy-dialog" :title="t('module.payment')" top="20vh" width="28%">
            <div v-loading="state.loading.buy">
                <el-alert :title="t('module.Module installation warning')" type="error" :center="true" :closable="false" />
                <div v-if="!isEmpty(state.buy.info)" class="order-info">
                    <div class="order-info-item">{{ t('module.Order title') }}：{{ state.buy.info.title }}</div>
                    <div class="order-info-item">{{ t('module.Order No') }}：{{ state.buy.info.sn }}</div>
                    <div class="order-info-item">{{ t('module.Purchase user') }}：{{ baAccount.nickname + '（' + baAccount.email + '）' }}</div>
                    <div class="order-info-item">
                        {{ t('module.Order price') }}：
                        <span v-if="!state.buy.info.purchased" class="order-price">{{ currency(state.buy.info.amount, 0) }}</span>
                        <span v-else class="order-price">{{ t('module.Purchased, can be installed directly') }}</span>
                    </div>
                    <div class="order-footer">
                        <div class="order-agreement">
                            <el-checkbox v-model="state.buy.agreement" size="small" label="" />
                            <span>
                                {{ t('module.Understand and agree') }}《
                                <a href="https://wonderful-code.gitee.io/guide/other/appendix/templateAgreement.html" target="_blank">
                                    {{ t('module.Module purchase and use agreement') }}
                                </a>
                                》
                            </span>
                        </div>
                        <div class="order-info-buttons">
                            <template v-if="!state.buy.info.purchased">
                                <el-button v-if="state.buy.info.pay.score" :loading="state.loading.common" @click="onPay(0)" v-blur type="warning">
                                    {{ t('module.Point payment') }}
                                </el-button>
                                <el-button v-if="state.buy.info.pay.money" :loading="state.loading.common" @click="onPay(1)" v-blur type="warning">
                                    {{ t('module.Balance payment') }}
                                </el-button>
                            </template>
                            <el-button
                                v-else
                                :loading="state.loading.common"
                                @click="onInstall(state.buy.info.uid, state.buy.info.id)"
                                v-blur
                                type="warning"
                            >
                                {{ t('module.Install now') }}
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { onPay, currency, onInstall } from '../index'
import { useBaAccount } from '/@/stores/baAccount'
import { useI18n } from 'vue-i18n'
import { isEmpty } from 'lodash-es'

const { t } = useI18n()
const baAccount = useBaAccount()
</script>

<style scoped lang="scss">
.order-info {
    padding: 10px 0;
    .order-info-item {
        padding-top: 6px;
    }
    .order-footer {
        padding-top: 20px;
        .order-agreement {
            display: flex;
            align-items: center;
            font-size: 12px;
            span {
                padding-left: 4px;
            }
            a {
                text-decoration: none;
                color: var(--el-color-primary);
            }
        }
        .order-info-buttons {
            padding-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
}
@media screen and (max-width: 1440px) {
    :deep(.buy-dialog) {
        --el-dialog-width: 26% !important;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.buy-dialog) {
        --el-dialog-width: 32% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.buy-dialog) {
        --el-dialog-width: 70% !important;
    }
}
</style>
