<template>
    <el-tabs
        v-loading="state.tableLoading"
        element-loading-text="加载中..."
        v-model="state.params.activeTab"
        type="border-card"
        class="store-tabs"
        @tab-change="onTabChange"
    >
        <el-tab-pane v-for="cat in state.category" :name="cat.id.toString()" :label="cat.name" class="store-tab-pane">
            <template v-if="state.modules[state.params.activeTab] && state.modules[state.params.activeTab].length > 0">
                <div class="goods" v-for="item in state.modules[state.params.activeTab]">
                    <div @click="showInfo(item.id)" class="goods-item suspension">
                        <el-image fit="contain" class="goods-img" :src="item.logo" />
                        <div class="goods-footer">
                            <div class="goods-tag" v-if="item.tags.length > 0">
                                <el-tag v-for="tag in item.tags" :type="tag.type">{{ tag.name }}</el-tag>
                            </div>
                            <div class="goods-title">
                                {{ item.title }}
                            </div>
                            <div class="goods-data">
                                <span class="download-count"> <Icon name="fa fa-download" color="#c0c4cc" size="13" /> {{ item.downloads }} </span>
                                <span class="goods-price">
                                    <span class="original-price">{{ currency(item.original_price, item.currency_select) }}</span>
                                    <span class="current-price">{{ currency(item.present_price, item.currency_select) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <el-empty v-else class="template-empty" description="没有更多了..." />
        </el-tab-pane>
    </el-tabs>
</template>

<script setup lang="ts">
import { state, loadData, currency, showInfo } from '../index'

const onTabChange = () => {
    loadData()
}
</script>

<style scoped lang="scss">
.store-tab-pane {
    display: flex;
    flex-wrap: wrap;
}
.goods {
    display: flex;
    flex-wrap: wrap;
}
.goods-item {
    width: 245px;
    margin: 10px;
    padding-bottom: 40px;
    position: relative;
    border-radius: var(--el-border-radius-base);
    background-color: var(--color-bg-2);
    cursor: pointer;
    box-shadow: var(--el-box-shadow-light);
}
.goods-img {
    border-radius: var(--el-border-radius-base);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    width: 245px;
    height: 163.33px;
}
.goods-tag .el-tag {
    margin: 0 6px 6px 0;
}
.template-empty {
    width: 100%;
}
.goods-footer {
    display: block;
    overflow: hidden;
    padding: 10px 10px 0 10px;
    .goods-title {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-top: 6px;
    }
    .goods-data {
        display: flex;
        width: calc(100% - 20px);
        position: absolute;
        bottom: 0;
        align-items: baseline;
        padding: 10px 0;
        .download-count {
            color: var(--color-placeholder);
        }
        .goods-price {
            margin-left: auto;
        }
        .original-price {
            font-size: 13px;
            color: var(--color-placeholder);
            text-decoration: line-through;
        }
        .current-price {
            font-size: 16px;
            color: var(--color-danger);
            padding-left: 6px;
        }
    }
}
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--color-bg-1);
    border-bottom: none;
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-radius: var(--el-border-radius-base);
}
</style>
