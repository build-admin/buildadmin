<template>
    <div>
        <el-dialog
            v-model="state.dialog.pay"
            :close-on-press-escape="false"
            :close-on-click-modal="false"
            class="pay-dialog"
            top="20vh"
            width="680px"
        >
            <div>
                <div class="header-box">
                    <img class="wechat-pay-logo" src="https://ba.buildadmin.com/static/images/wechat-pay.png" alt="" />
                </div>
                <div class="pay-box">
                    <div class="left">
                        <div class="order-info">
                            <div class="order-info-items">{{ t('module.Order title') }}：{{ state.payInfo.info.title }}</div>
                            <div class="order-info-items">{{ t('module.Order No') }}：{{ state.payInfo.info.sn }}</div>
                            <div class="order-info-items">
                                {{ t('module.Purchase user') }}：{{ baAccount.nickname + '（' + baAccount.email + '）' }}
                            </div>
                            <div class="order-info-items">
                                {{ t('module.Order price') }}：<span class="rmb-symbol"
                                    >￥<span class="amount">{{ state.payInfo.info.amount }}</span></span
                                >
                            </div>
                        </div>
                        <div class="pay_qr">
                            <vue-qr :text="state.payInfo.pay.code_url" :size="220" :margin="0"></vue-qr>
                            <div v-if="state.payInfo.pay.status == 'success'" class="pay-success">
                                <Icon name="fa fa-check" color="var(--el-color-success)" size="30" />
                            </div>
                        </div>
                        <el-alert class="qr-tips" :closable="false" type="success" center>
                            <div class="qr-tips-content">
                                <Icon color="var(--el-color-success)" name="fa fa-wechat" />
                                <span>{{ t('module.Use WeChat to scan QR code for payment') }}</span>
                            </div>
                        </el-alert>
                    </div>
                    <div class="right">
                        <img class="wechat-pay-logo" src="https://ba.buildadmin.com/static/images/screenshot-wechat.png" alt="" />
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { useI18n } from 'vue-i18n'
import { useBaAccount } from '/@/stores/baAccount'
import vueQr from 'vue-qr/src/packages/vue-qr.vue'

const { t } = useI18n()
const baAccount = useBaAccount()
</script>

<style scoped lang="scss">
:deep(.pay-dialog) .el-dialog__body {
    padding: var(--el-dialog-padding-primary);
    padding-top: 0;
}
.header-box {
    .wechat-pay-logo {
        height: 30px;
        user-select: none;
    }
    padding-bottom: 10px;
    border-bottom: 1px solid var(--el-border-color-lighter);
}
.pay-box {
    display: flex;
    .right {
        margin-left: auto;
    }
}
.order-info {
    padding: 15px 0;
    .order-info-items {
        line-height: 24px;
        .rmb-symbol {
            color: var(--el-color-danger);
            font-size: 13px;
        }
        .amount {
            color: var(--el-color-danger);
            font-size: 16px;
        }
    }
}
.pay_qr {
    display: flex;
    margin-bottom: 25px;
    justify-content: center;
    position: relative;
    .pay-success {
        border-radius: 50%;
        border: 3px solid rgba($color: #67c23a, $alpha: 0.8);
        padding: 5px;
        position: absolute;
        left: calc(50% - 15px);
        top: calc(50% - 15px);
    }
}
.qr-tips {
    margin-top: 15px;
    .qr-tips-content {
        .icon {
            margin-right: 5px;
        }
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
@media screen and (max-width: 700px) {
    :deep(.pay-dialog) {
        --el-dialog-width: 96% !important;
    }
    .pay-box .right {
        display: none;
    }
}
</style>
