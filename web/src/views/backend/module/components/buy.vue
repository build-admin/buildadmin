<template>
    <div>
        <el-dialog v-model="state.buy.showDialog" custom-class="buy-dialog" title="支付" top="20vh" width="28%">
            <div v-loading="state.buy.showLoading">
                <el-alert title="购买后一年内可免费下载和更新，虚拟产品不支持7天无理由退款" type="error" :center="true" :closable="false" />
                <div v-if="!isEmpty(state.buy.info)" class="order-info">
                    <div class="order-info-item">订单标题：{{ state.buy.info.title }}</div>
                    <div class="order-info-item">订单编号：{{ state.buy.info.sn }}</div>
                    <div class="order-info-item">购买用户：{{ baAccount.nickname + '（' + baAccount.email + '）' }}</div>
                    <div class="order-info-item">
                        订单价格：<span class="order-price">{{ currency(state.buy.info.amount, 0) }}</span>
                    </div>
                    <div class="order-footer">
                        <div class="order-agreement">
                            <el-checkbox v-model="state.buy.agreement" size="small" label="" />
                            <span>
                                理解并同意《
                                <a href="https://wonderful-code.gitee.io/senior/other/templateAgreement.html" target="_blank">
                                    模板案例购买和使用协议
                                </a>
                                》
                            </span>
                        </div>
                        <div class="order-info-buttons">
                            <template v-if="!state.buy.info.purchased">
                                <el-button
                                    v-if="state.buy.info.pay.score"
                                    :loading="state.publicButtonLoading"
                                    @click="onPay(0)"
                                    v-blur
                                    type="warning"
                                    >积分支付</el-button
                                >
                                <el-button
                                    v-if="state.buy.info.pay.money"
                                    :loading="state.publicButtonLoading"
                                    @click="onPay(1)"
                                    v-blur
                                    type="warning"
                                    >余额支付</el-button
                                >
                            </template>
                            <el-button
                                v-else
                                :loading="state.publicButtonLoading"
                                @click="onInstall(state.buy.info.uid, state.buy.info.id)"
                                v-blur
                                type="warning"
                                >立即安装</el-button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state, onPay, currency, onInstall } from '../index'
import { useBaAccount } from '/@/stores/baAccount'
import { isEmpty } from 'lodash'

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
                color: var(--color-primary);
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
