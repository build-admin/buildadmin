<template>
    <div>
        <el-dialog v-model="state.goodsInfo.showDialog" custom-class="goods-info-dialog" title="详细信息" width="55%">
            <el-scrollbar v-loading="state.goodsInfo.loading" :key="state.goodsInfo.info.uid" :height="500">
                <div class="goods-info">
                    <div class="goods-images">
                        <el-carousel height="300" v-if="state.goodsInfo.info.images" indicator-position="outside">
                            <el-carousel-item v-for="(image, idx) in state.goodsInfo.info.images" :key="idx">
                                <el-image fit="contain" :src="image"></el-image>
                            </el-carousel-item>
                        </el-carousel>
                    </div>
                    <div class="goods-basic">
                        <h4 class="goods-basic-title">{{ state.goodsInfo.info.title }}</h4>
                        <div class="goods-tag">
                            <el-tag v-for="tag in state.goodsInfo.info.tags" :type="tag.type">{{ tag.name }}</el-tag>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">价格</div>
                            <div class="basic-item-price">
                                {{ currency(state.goodsInfo.info.present_price, state.goodsInfo.info.currency_select) }}
                            </div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">最后更新</div>
                            <div class="basic-item-content">{{ timeFormat(state.goodsInfo.info.updatetime) }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">发布时间</div>
                            <div class="basic-item-content">{{ timeFormat(state.goodsInfo.info.createtime) }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">下载次数</div>
                            <div class="basic-item-content">{{ state.goodsInfo.info.downloads }}</div>
                        </div>
                        <div v-if="state.goodsInfo.info.category" class="basic-item">
                            <div class="basic-item-title">模板分类</div>
                            <div class="basic-item-content">{{ state.goodsInfo.info.category.name }}</div>
                        </div>
                        <div class="basic-item">
                            <div class="basic-item-title">开发者主页</div>
                            <div class="basic-item-content">
                                <a v-if="state.goodsInfo.info.author_url" :href="state.goodsInfo.info.author_url">点击访问</a>
                                <span v-else>无</span>
                            </div>
                        </div>
                        <div class="basic-buttons">
                            <el-dropdown v-if="state.goodsInfo.info.demo && state.goodsInfo.info.demo.length > 0">
                                <el-button class="basic-button-demo" type="primary">
                                    <span class="basic-button-dropdown-span">查看演示</span>
                                    <Icon color="#ffffff" size="16" name="el-icon-ArrowDown" />
                                </el-button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item
                                            v-for="demo in state.goodsInfo.info.demo"
                                            @click="openDemo(demo.link, demo.image ? false : true)"
                                            class="basic-button-dropdown-item"
                                        >
                                            <el-popover
                                                placement="right"
                                                title="扫码预览"
                                                trigger="hover"
                                                :disabled="demo.image ? false : true"
                                                :width="174"
                                            >
                                                <template #reference>
                                                    <div class="demo-item-title">
                                                        <Icon :name="demo.icon" size="14" color="var(--color-text-primary)" />{{ demo.title }}
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
                            <el-button v-if="!state.goodsInfo.info.purchased" @click="onBuy" v-blur class="basic-button-item" type="danger"
                                >立即购买</el-button
                            >
                            <template v-else>
                                <el-button
                                    v-if="installButtonText['Install now'].includes(state.goodsInfo.info.state)"
                                    @click="onInstall(state.goodsInfo.info.uid, state.goodsInfo.info.purchased)"
                                    :loading="state.publicButtonLoading"
                                    v-blur
                                    class="basic-button-item"
                                    type="success"
                                    >立即安装</el-button
                                >
                                <el-button
                                    v-if="installButtonText['continue installation'].includes(state.goodsInfo.info.state)"
                                    @click="onInstall(state.goodsInfo.info.uid, state.goodsInfo.info.purchased)"
                                    :loading="state.publicButtonLoading"
                                    v-blur
                                    class="basic-button-item"
                                    type="success"
                                    >继续安装</el-button
                                >
                                <el-button
                                    v-if="installButtonText['already installed'].includes(state.goodsInfo.info.state)"
                                    v-blur
                                    :disabled="true"
                                    class="basic-button-item"
                                    >已安装 v{{ state.goodsInfo.info.version }}</el-button
                                >
                            </template>
                        </div>
                    </div>
                    <div v-if="!isEmpty(state.goodsInfo.info.developer)" class="goods-developer">
                        <div class="developer-header">
                            <el-avatar :size="60" :src="state.goodsInfo.info.developer.avatar" />
                            <div class="developer-name">
                                <h3 class="developer-nickname">{{ state.goodsInfo.info.developer.nickname }}</h3>
                                <div class="developer-group">
                                    {{ state.goodsInfo.info.developer.group ? state.goodsInfo.info.developer.group : '无' }}
                                </div>
                            </div>
                        </div>
                        <div v-if="state.goodsInfo.info.qq" class="developer-contact">
                            <h4 class="developer-info-title">联系开发者</h4>
                            <div class="contact-item">
                                <a target="_blank" :href="'http://wpa.qq.com/msgrd?v=3&uin=' + state.goodsInfo.info.qq + '&site=qq&menu=yes'"
                                    >QQ：{{ state.goodsInfo.info.qq }}</a
                                >
                            </div>
                        </div>
                        <div class="developer-recommend">
                            <h4 class="developer-info-title">TA的其他作品</h4>
                            <div v-if="state.goodsInfo.info.developer.goods.length > 0" class="recommend-goods">
                                <div
                                    v-for="goods_item in state.goodsInfo.info.developer.goods"
                                    @click="showInfo(goods_item.id)"
                                    class="recommend-goods-item"
                                >
                                    <el-image fit="contain" class="recommend-goods-logo" :src="goods_item.logo"> </el-image>
                                    <div class="recommend-goods-title">{{ goods_item.title }}</div>
                                </div>
                            </div>
                            <div v-else class="data-empty">没有更多作品了</div>
                        </div>
                    </div>
                </div>
                <div class="goods-detail" v-html="state.goodsInfo.info.detail_editor"></div>
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state, moduleInstallState, showInfo, currency, onBuy, onInstall } from '../index'
import { timeFormat } from '/@/components/table'
import { isEmpty } from 'lodash'

const installButtonText = {
    'Install now': [moduleInstallState.UNINSTALLED, moduleInstallState.WAIT_INSTALL],
    'continue installation': [moduleInstallState.CONFLICT_PENDING, moduleInstallState.DEPENDENT_WAIT_INSTALL],
    'already installed': [moduleInstallState.INSTALLED],
}

const openDemo = (url: string, open: boolean) => {
    if (!open) return
    if (!url) return
    window.open(url)
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
    .goods-images {
        max-width: 41%;
        width: 300px;
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
            padding: 5px 0;
            .basic-item-title {
                font-size: var(--el-font-size-base);
                color: var(--color-secondary);
                width: 80px;
            }
            .basic-item-content {
                font-size: var(--el-font-size-base);
                color: var(--color-regular);
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
        border-left: 1px solid var(--color-bg-1);
        padding: 10px;
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
                    color: var(--color-secondary);
                }
            }
        }
        .developer-info-title {
            color: var(--color-secondary);
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
                color: var(--color-primary);
                text-decoration: none;
            }
        }
        .recommend-goods-item {
            display: flex;
            align-items: center;
            margin-top: 2px;
            cursor: pointer;
            padding: 2px;
            &:hover {
                background-color: var(--color-bg-1);
            }
            .recommend-goods-logo {
                width: 34px;
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
                color: var(--color-secondary);
                text-align: center;
                padding: 6px;
            }
        }
    }
    .el-carousel__item:nth-child(2n) {
        background-color: #99a9bf;
    }
}
/* 商品详情弹窗-s */
@media screen and (max-width: 1440px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 60% !important;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 76% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.goods-info-dialog) {
        --el-dialog-width: 92% !important;
    }
}
/* 商品详情弹窗-e */
@media screen and (max-width: 860px) {
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
