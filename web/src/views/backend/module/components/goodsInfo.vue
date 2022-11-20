<template>
    <div>
        <el-dialog v-model="state.dialog.goodsInfo" class="goods-info-dialog" :title="t('module.detailed information')" width="60%">
            <el-scrollbar v-loading="state.loading.goodsInfo" :key="state.goodsInfo.uid" :height="500">
                <div class="goods-info">
                    <div class="goods-images">
                        <el-carousel height="300" v-if="state.goodsInfo.images" indicator-position="outside">
                            <el-carousel-item class="goods-image-item" v-for="(image, idx) in state.goodsInfo.images" :key="idx">
                                <el-image fit="contain" :preview-src-list="state.goodsInfo.images" :preview-teleported="true" :src="image"></el-image>
                            </el-carousel-item>
                        </el-carousel>
                    </div>
                    <div class="goods-basic">
                        <h4 class="goods-basic-title">{{ state.goodsInfo.title }}</h4>
                        <div class="goods-tag">
                            <el-tag v-for="(tag, idx) in state.goodsInfo.tags" :key="idx" :type="tag.type">{{ tag.name }}</el-tag>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">{{ t('module.Price') }}</div>
                            <div class="basic-item-price">
                                {{
                                    typeof state.goodsInfo.currency_select != 'undefined'
                                        ? currency(state.goodsInfo.present_price, state.goodsInfo.currency_select)
                                        : '-'
                                }}
                            </div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">{{ t('module.Last updated') }}</div>
                            <div class="basic-item-content">{{ state.goodsInfo.updatetime ? timeFormat(state.goodsInfo.updatetime) : '-' }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">{{ t('module.Published on') }}</div>
                            <div class="basic-item-content">{{ state.goodsInfo.createtime ? timeFormat(state.goodsInfo.createtime) : '-' }}</div>
                        </div>
                        <div v-if="!installButtonState.stateSwitch.includes(state.goodsInfo.state)" class="basic-item">
                            <div class="basic-item-title">{{ t('module.amount of downloads') }}</div>
                            <div class="basic-item-content">{{ state.goodsInfo.downloads ? state.goodsInfo.downloads : '-' }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">{{ t('module.Module classification') }}</div>
                            <div class="basic-item-content">{{ state.goodsInfo.category ? state.goodsInfo.category.name : '-' }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">{{ t('module.Developer Homepage') }}</div>
                            <div class="basic-item-content">
                                <el-link
                                    type="primary"
                                    class="developer-homepage"
                                    v-if="state.goodsInfo.author_url"
                                    target="_blank"
                                    :href="state.goodsInfo.author_url"
                                >
                                    {{ t('module.Click to access') }}
                                </el-link>
                                <span v-else>-</span>
                            </div>
                        </div>
                        <div v-if="installButtonState.stateSwitch.includes(state.goodsInfo.state)" class="basic-item">
                            <div class="basic-item-title">{{ t('module.Module status') }}</div>
                            <div class="basic-item-content">
                                <el-switch
                                    @change="onChangeState"
                                    :loading="state.loading.common"
                                    :disabled="state.loading.common"
                                    v-model="state.goodsInfo.enable"
                                />
                            </div>
                        </div>
                        <div class="basic-buttons">
                            <el-dropdown
                                v-if="
                                    (!state.goodsInfo.purchased || installButtonState.InstallNow.includes(state.goodsInfo.state)) &&
                                    state.goodsInfo.demo &&
                                    state.goodsInfo.demo.length > 0
                                "
                            >
                                <el-button class="basic-button-demo" type="primary">
                                    <span class="basic-button-dropdown-span">{{ t('module.View demo') }}</span>
                                    <Icon color="#ffffff" size="16" name="el-icon-ArrowDown" />
                                </el-button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item
                                            v-for="(demo, idx) in state.goodsInfo.demo"
                                            :key="idx"
                                            @click="openDemo(demo.link, demo.image ? false : true)"
                                            class="basic-button-dropdown-item"
                                        >
                                            <el-popover
                                                placement="right"
                                                :title="t('module.Code scanning Preview')"
                                                trigger="hover"
                                                :disabled="demo.image ? false : true"
                                                :width="174"
                                            >
                                                <template #reference>
                                                    <div class="demo-item-title">
                                                        <Icon :name="demo.icon" size="14" color="var(--el-color-primary)" />{{ demo.title }}
                                                    </div>
                                                </template>
                                                <div class="demo-image">
                                                    <img :src="demo.image" alt="" />
                                                </div>
                                            </el-popover>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                            <el-button
                                v-if="
                                    !state.goodsInfo.purchased &&
                                    installButtonState.buy.includes(state.goodsInfo.state) &&
                                    state.goodsInfo.type == 'online'
                                "
                                @click="onBuy"
                                v-blur
                                class="basic-button-item"
                                type="danger"
                            >
                                {{ t('module.Buy now') }}
                            </el-button>
                            <el-button
                                v-if="
                                    (state.goodsInfo.state == moduleInstallState.UNINSTALLED && state.goodsInfo.purchased) ||
                                    state.goodsInfo.state == moduleInstallState.WAIT_INSTALL
                                "
                                @click="onInstall(state.goodsInfo.uid, state.goodsInfo.purchased)"
                                :loading="state.loading.common"
                                v-blur
                                class="basic-button-item"
                                type="success"
                            >
                                {{ t('module.Install now') }}
                            </el-button>
                            <el-button
                                v-if="installButtonState.continueInstallation.includes(state.goodsInfo.state)"
                                @click="onInstall(state.goodsInfo.uid, state.goodsInfo.purchased)"
                                :loading="state.loading.common"
                                v-blur
                                class="basic-button-item"
                                type="success"
                            >
                                {{ t('module.continue installation') }}
                            </el-button>
                            <el-button
                                v-if="installButtonState.alreadyInstalled.includes(state.goodsInfo.state)"
                                v-blur
                                :disabled="true"
                                class="basic-button-item"
                            >
                                {{ t('module.installed') }} v{{ state.goodsInfo.version }}
                            </el-button>
                            <el-button
                                v-if="state.goodsInfo.type == 'local' && !installButtonState.alreadyInstalled.includes(state.goodsInfo.state)"
                                v-blur
                                :disabled="true"
                                class="basic-button-item"
                            >
                                {{ t('module.Local module') }} v{{ state.goodsInfo.version }}
                            </el-button>
                            <el-button
                                v-if="state.goodsInfo.new_version && installButtonState.updateButton.includes(state.goodsInfo.state)"
                                @click="onUpdate(state.goodsInfo.uid, state.goodsInfo.purchased)"
                                v-loading="state.loading.common"
                                v-blur
                                class="basic-button-item"
                                type="success"
                            >
                                {{ t('module.to update') }}
                            </el-button>
                            <el-button
                                v-if="installButtonState.stateSwitch.includes(state.goodsInfo.state)"
                                v-loading="state.loading.common"
                                @click="unInstall(state.goodsInfo.uid)"
                                v-blur
                                class="basic-button-item"
                                type="danger"
                            >
                                {{ t('module.uninstall') }}
                            </el-button>
                        </div>
                    </div>
                    <div v-if="!isEmpty(state.goodsInfo.developer)" class="goods-developer">
                        <div class="developer-header">
                            <el-avatar :size="60" :src="state.goodsInfo.developer.avatar" />
                            <div class="developer-name">
                                <h3 class="developer-nickname">{{ state.goodsInfo.developer.nickname }}</h3>
                                <div class="developer-group">
                                    {{ state.goodsInfo.developer.group ? state.goodsInfo.developer.group : '-' }}
                                </div>
                            </div>
                        </div>
                        <div v-if="state.goodsInfo.qq" class="developer-contact">
                            <h4 class="developer-info-title">{{ t('module.Contact developer') }}</h4>
                            <div class="contact-item">
                                <a target="_blank" :href="'http://wpa.qq.com/msgrd?v=3&uin=' + state.goodsInfo.qq + '&site=qq&menu=yes'"
                                    >QQ：{{ state.goodsInfo.qq }}</a
                                >
                            </div>
                        </div>
                        <div class="developer-recommend">
                            <h4 class="developer-info-title">{{ t('module.Other works of developers') }}</h4>
                            <div v-if="state.goodsInfo.developer.goods.length > 0" class="recommend-goods">
                                <div
                                    v-for="(goods_item, idx) in state.goodsInfo.developer.goods"
                                    :key="idx"
                                    @click="showInfo(goods_item.uid)"
                                    class="recommend-goods-item"
                                >
                                    <el-image fit="contain" class="recommend-goods-logo" :src="goods_item.logo"> </el-image>
                                    <div class="recommend-goods-title">{{ goods_item.title }}</div>
                                </div>
                            </div>
                            <div v-else class="data-empty">{{ t('module.There are no more works') }}</div>
                        </div>
                    </div>
                </div>
                <div class="goods-detail ba-markdown" v-html="state.goodsInfo.detail_editor"></div>
                <div class="goods-version">
                    <h1>{{ t('module.Update Log') }}</h1>
                    <div class="version-timeline" v-if="state.goodsInfo.version_log">
                        <el-timeline>
                            <el-timeline-item
                                v-for="(version, idx) in state.goodsInfo.version_log"
                                :key="idx"
                                :timestamp="timeFormat(version.createtime)"
                                placement="top"
                                :color="idx == 0 ? 'var(--el-color-success)' : ''"
                            >
                                <el-card class="version-card" shadow="hover">
                                    <template #header>
                                        <div class="version-card-header">
                                            <h2>{{ version.title }}</h2>
                                            <span class="version-short-describe">{{ version.short_describe }}</span>
                                        </div>
                                    </template>
                                    <div
                                        class="version-detail ba-markdown"
                                        v-html="version.describe ? version.describe : t('module.No detailed update log')"
                                    ></div>
                                </el-card>
                            </el-timeline-item>
                        </el-timeline>
                    </div>
                    <div v-else class="empty-update-log">{{ $t('module.No detailed update log') }}</div>
                </div>
            </el-scrollbar>
        </el-dialog>
        <Buy />
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { showInfo, currency, onBuy, onInstall, onDisable, onEnable, onRefreshTableData, loginExpired } from '../index'
import { postUninstall, getInstallState, postUpdate } from '/@/api/backend/module'
import { moduleInstallState } from '../types'
import { timeFormat } from '/@/components/table'
import { isEmpty } from 'lodash-es'
import { ElMessageBox } from 'element-plus'
import { useBaAccount } from '/@/stores/baAccount'
import { useI18n } from 'vue-i18n'
import Buy from './buy.vue'

const installButtonState = {
    InstallNow: [moduleInstallState.UNINSTALLED, moduleInstallState.WAIT_INSTALL],
    continueInstallation: [moduleInstallState.CONFLICT_PENDING, moduleInstallState.DEPENDENT_WAIT_INSTALL],
    alreadyInstalled: [moduleInstallState.INSTALLED],
    stateSwitch: [
        moduleInstallState.INSTALLED,
        moduleInstallState.CONFLICT_PENDING,
        moduleInstallState.DEPENDENT_WAIT_INSTALL,
        moduleInstallState.DISABLE,
    ],
    updateButton: [moduleInstallState.WAIT_INSTALL, moduleInstallState.INSTALLED, moduleInstallState.DISABLE],
    buy: [moduleInstallState.UNINSTALLED],
}

const { t } = useI18n()
const openDemo = (url: string, open: boolean) => {
    if (!open || !url) return
    window.open(url)
}

const onChangeState = () => {
    if (state.goodsInfo.enable) {
        onEnable(state.goodsInfo.uid)
    } else {
        state.common.disableParams = {
            uid: state.goodsInfo.uid,
            state: 0,
        }
        onDisable()
    }
}

const unInstall = (uid: string) => {
    state.loading.common = true
    postUninstall(uid)
        .then(() => {
            onRefreshTableData()
            state.dialog.goodsInfo = false
        })
        .finally(() => {
            state.loading.common = false
        })
}

const onUpdate = (uid: string, order: number) => {
    const baAccount = useBaAccount()
    if (!baAccount.token) {
        state.dialog.baAccount = true
        return
    }
    state.loading.common = true
    getInstallState(uid)
        .then((res) => {
            if (res.data.state == moduleInstallState.DISABLE) {
                postUpdate(uid, order)
                    .then(() => {
                        onInstall(uid, order)
                    })
                    .catch((res) => {
                        if (loginExpired(res)) return
                    })
            } else {
                ElMessageBox.confirm(t('module.You need to disable this module before updating Do you want to disable it now?'), t('Reminder'), {
                    confirmButtonText: t('module.Disable and update'),
                    cancelButtonText: t('Cancel'),
                    type: 'warning',
                })
                    .then(() => {
                        state.common.disableParams = {
                            uid: uid,
                            state: 0,
                            upadte: 1,
                            order: order,
                            token: baAccount.token,
                        }
                        onDisable()
                    })
                    .catch(() => {})
            }
        })
        .finally(() => {
            state.loading.common = false
        })
}
</script>

<style scoped lang="scss">
:deep(.goods-info-dialog) .el-dialog__body {
    padding: 0px 20px;
}
.demo-image,
.demo-image img {
    width: 150px;
    height: 150px;
}
.demo-item-title {
    display: flex;
    align-items: center;

    .icon {
        margin-right: 6px;
    }
}
.goods-info {
    display: flex;
    position: relative;
    .goods-images {
        max-width: 41%;
        width: 300px;
        .goods-image-item {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
    .goods-basic {
        position: relative;

        .goods-basic-title {
            padding-bottom: 20px;
        }
        flex: 1;
        padding: 0 10px;
        .basic-item {
            display: flex;
            align-items: center;
            padding: 5px 0;
            .basic-item-title {
                font-size: var(--el-font-size-base);
                color: var(--el-text-color-secondary);
                width: 80px;
            }
            .basic-item-content {
                font-size: var(--el-font-size-base);
                color: var(--el-text-color-regular);
            }
        }
        .basic-button-dropdown-span {
            padding-right: 6px;
        }
        .basic-buttons {
            position: absolute;
            bottom: 26px;
            padding-top: 3px;
        }
        .basic-button-demo {
            margin-right: 10px;
        }
    }
    .goods-developer {
        width: 20%;
        border-left: 1px solid var(--ba-bg-color);
        padding: 10px;
        position: absolute;
        right: 0;
        .developer-header {
            display: flex;
            align-items: center;
            justify-content: center;
            .developer-name {
                padding-left: 10px;
                flex: 1;
                .developer-group {
                    padding-top: 5px;
                    font-size: var(--el-font-size-extra-small);
                    color: var(--el-text-color-secondary);
                }
            }
        }
        .developer-info-title {
            color: var(--el-text-color-secondary);
            padding-top: 15px;
            line-height: 20px;
            text-align: center;
        }
        .contact-item {
            cursor: pointer;
            padding-left: 10px;
            line-height: 30px;
            text-align: center;
            a {
                color: var(--el-color-primary);
                text-decoration: none;
            }
        }
        .recommend-goods-item {
            display: flex;
            align-items: center;
            margin: 4px 0;
            cursor: pointer;
            padding: 6px;
            &:hover {
                background-color: var(--ba-bg-color);
            }
            .recommend-goods-logo {
                width: 42px;
                border-radius: var(--el-border-radius-base);
            }
            .recommend-goods-title {
                flex: 1;
                margin-left: 6px;
                font-size: var(--el-font-size-small);
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                line-height: 15px;
                height: 28px;
            }
        }
        .developer-recommend {
            .data-empty {
                font-size: var(--el-font-size-extra-small);
                color: var(--el-text-color-secondary);
                text-align: center;
                padding: 6px;
            }
        }
    }
    .el-carousel__item:nth-child(2n) {
        background-color: #99a9bf;
    }
    .developer-homepage {
        font-size: var(--el-font-size-small);
    }
}
.basic-button-item {
    --el-loading-spinner-size: 22px;
}
.goods-detail {
    width: 80%;
}
.goods-version {
    width: 80%;
    h1 {
        margin: 1.4em 0 0.8em;
        font-weight: 700;
        font-size: var(--el-font-size-large);
        text-transform: uppercase;
        color: var(--el-color-primary);
    }
    .version-timeline {
        padding-left: 2px;
        :deep(.el-card__body) {
            padding: 10px 20px 20px 20px;
        }
    }
    .version-card {
        border: 1px solid var(--el-color-info-light-9);
    }
    .version-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
.empty-update-log {
    display: flex;
    justify-content: center;
    color: var(--el-color-info);
}
/* 商品详情弹窗-s */
@media screen and (max-width: 1440px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 65% !important;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 80% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 92% !important;
    }
}
/* 商品详情弹窗-e */
@media screen and (max-width: 865px) {
    .goods-info .goods-developer {
        display: none;
    }
}
@media screen and (max-width: 540px) {
    .goods-info {
        flex-wrap: wrap;
        .goods-images {
            max-width: 100%;
            width: 100%;
        }
        .goods-basic .basic-buttons {
            position: unset;
        }
    }
    .goods-detail {
        padding-top: 15px;
    }
}
</style>
