<template>
    <div class="layout-config-drawer">
        <el-drawer v-model="config.showDrawer" title="布局配置" size="310px" @close="onCloseDrawer">
            <el-scrollbar class="layout-mode-style-scrollbar">
                <el-form ref="formRef" :model="config">
                    <div class="layout-mode-styles-box">
                        <el-divider border-style="dashed">布局方式</el-divider>
                        <div class="layout-mode-box-style">
                            <el-row class="layout-mode-box-style-row" :gutter="10">
                                <el-col :span="12">
                                    <div
                                        @click="configStore.setLayoutMode('Default')"
                                        class="layout-mode-style default"
                                        :class="config.layoutMode == 'Default' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-aside"></div>
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">默认</div>
                                    </div>
                                </el-col>
                                <el-col :span="12">
                                    <div
                                        @click="configStore.setLayoutMode('Classic')"
                                        class="layout-mode-style classic"
                                        :class="config.layoutMode == 'Classic' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-aside"></div>
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">经典</div>
                                    </div>
                                </el-col>
                            </el-row>
                            <el-row :gutter="10">
                                <el-col :span="12">
                                    <div
                                        @click="configStore.setLayoutMode('Streamline')"
                                        class="layout-mode-style streamline"
                                        :class="config.layoutMode == 'Streamline' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">单栏</div>
                                    </div>
                                </el-col>
                            </el-row>
                        </div>
                        <el-divider border-style="dashed">全局</el-divider>
                        <div class="layout-config-global">
                            <el-form-item label="后台页面切换动画">
                                <el-select
                                    @change="onCommitState($event, 'mainAnimation')"
                                    v-model="config.mainAnimation"
                                    placeholder="请选择动画名称"
                                >
                                    <el-option label="slide-right" value="slide-right"></el-option>
                                    <el-option label="slide-left" value="slide-left"></el-option>
                                    <el-option label="el-fade-in-linear" value="el-fade-in-linear"></el-option>
                                    <el-option label="el-fade-in" value="el-fade-in"></el-option>
                                    <el-option label="el-zoom-in-center" value="el-zoom-in-center"></el-option>
                                    <el-option label="el-zoom-in-top" value="el-zoom-in-top"></el-option>
                                    <el-option label="el-zoom-in-bottom" value="el-zoom-in-bottom"></el-option>
                                </el-select>
                            </el-form-item>
                        </div>

                        <el-divider border-style="dashed">侧边栏</el-divider>
                        <div class="layout-config-aside">
                            <el-form-item label="侧边菜单栏背景色">
                                <el-color-picker @change="onCommitState($event, 'menuBackground')" v-model="config.menuBackground" />
                            </el-form-item>
                            <el-form-item label="侧边菜单文字颜色">
                                <el-color-picker @change="onCommitState($event, 'menuColor')" v-model="config.menuColor" />
                            </el-form-item>
                            <el-form-item label="侧边菜单激活项背景色">
                                <el-color-picker @change="onCommitState($event, 'menuActiveBackground')" v-model="config.menuActiveBackground" />
                            </el-form-item>
                            <el-form-item label="侧边菜单激活项文字色">
                                <el-color-picker @change="onCommitState($event, 'menuActiveColor')" v-model="config.menuActiveColor" />
                            </el-form-item>
                            <el-form-item label="显示侧边菜单顶栏(LOGO栏)">
                                <el-switch @change="onCommitState($event, 'menuShowTopBar')" v-model="config.menuShowTopBar"></el-switch>
                            </el-form-item>
                            <el-form-item label="侧边菜单顶栏背景色">
                                <el-color-picker @change="onCommitState($event, 'menuTopBarBackground')" v-model="config.menuTopBarBackground" />
                            </el-form-item>
                            <el-form-item label="侧边菜单宽度(展开时)">
                                <el-input @input="onCommitState($event, 'menuWidth')" type="number" :step="10" v-model="config.menuWidth">
                                    <template #append>px</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="侧边菜单默认图标">
                                <selector @change="onCommitMenuDefaultIcon($event, 'menuDefaultIcon')" v-model="config.menuDefaultIcon" />
                            </el-form-item>
                            <el-form-item label="侧边菜单水平折叠">
                                <el-switch @change="onCommitState($event, 'menuCollapse')" v-model="config.menuCollapse"></el-switch>
                            </el-form-item>
                            <el-form-item label="侧边菜单手风琴">
                                <el-switch @change="onCommitState($event, 'menuUniqueOpened')" v-model="config.menuUniqueOpened"></el-switch>
                            </el-form-item>
                        </div>

                        <el-divider border-style="dashed">顶栏</el-divider>
                        <div class="layout-config-aside">
                            <el-form-item label="顶栏背景色">
                                <el-color-picker @change="onCommitState($event, 'headerBarBackground')" v-model="config.headerBarBackground" />
                            </el-form-item>
                            <el-form-item label="顶栏文字色">
                                <el-color-picker @change="onCommitState($event, 'headerBarTabColor')" v-model="config.headerBarTabColor" />
                            </el-form-item>
                            <el-form-item label="顶栏悬停时背景色">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarHoverBackground')"
                                    v-model="config.headerBarHoverBackground"
                                />
                            </el-form-item>
                            <el-form-item label="顶栏菜单激活项背景色">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarTabActiveBackground')"
                                    v-model="config.headerBarTabActiveBackground"
                                />
                            </el-form-item>
                            <el-form-item label="顶栏菜单激活项文字色">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarTabActiveColor')"
                                    v-model="config.headerBarTabActiveColor"
                                />
                            </el-form-item>
                        </div>
                        <el-popconfirm @confirm="restoreDefault" title="确定要恢复全部配置到默认值吗?">
                            <template #reference>
                                <div class="ba-center">
                                    <el-button class="w80" type="info">恢复默认</el-button>
                                </div>
                            </template>
                        </el-popconfirm>
                    </div>
                </el-form>
            </el-scrollbar>
        </el-drawer>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useRouter } from 'vue-router'
import selector from '/@/components/icon/selector.vue'
import { STORE_CONFIG } from '/@/stores/constant/cacheKey'
import { Local } from '/@/utils/storage'

const configStore = useConfig()
const navTabs = useNavTabs()
const router = useRouter()

const config = computed(() => configStore.layout)

const onCommitState = (value: any, name: any) => {
    configStore.setLayout(name, value)
}

// 修改默认菜单图标
const onCommitMenuDefaultIcon = (value: any, name: any) => {
    configStore.setLayout(name, value)

    const menus = navTabs.state.tabsViewRoutes
    navTabs.setTabsViewRoutes([])
    setTimeout(() => {
        navTabs.setTabsViewRoutes(menus)
    }, 200)
}

const onCloseDrawer = () => {
    configStore.setLayout('showDrawer', false)
}

const restoreDefault = (): boolean => {
    Local.remove(STORE_CONFIG)
    router.go(0)
    return true
}
</script>

<style scoped lang="scss">
.layout-config-drawer :deep(.el-input__inner) {
    padding: 0 0 0 6px;
}
.layout-config-drawer :deep(.el-input-group__append) {
    padding: 0 10px;
}
.layout-config-drawer :deep(.el-drawer__header) {
    margin-bottom: 0 !important;
}
.layout-config-drawer :deep(.el-drawer__body) {
    padding: 0;
}
.layout-mode-styles-box {
    padding: 20px;
}
.layout-mode-box-style-row {
    margin-bottom: 15px;
}
.layout-mode-style {
    position: relative;
    height: 100px;
    border: 1px solid var(--color-sub-2);
    border-radius: var(--el-border-radius-small);
    &:hover,
    &.active {
        border: 1px solid var(--color-primary);
    }
    .layout-mode-style-name {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary-sub-5);
        border-radius: 50%;
        height: 50px;
        width: 50px;
        border: 1px solid var(--color-primary-sub-4);
    }
    .layout-mode-style-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    &.default {
        display: flex;
        align-items: center;
        justify-content: center;
        .layout-mode-style-aside {
            width: 18%;
            height: 90%;
            background-color: var(--color-sub-3);
        }
        .layout-mode-style-container-box {
            width: 68%;
            height: 90%;
            margin-left: 4%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--color-sub-3);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 85%;
                background-color: var(--color-sub-4);
                margin-top: 5%;
            }
        }
    }
    &.classic {
        display: flex;
        align-items: center;
        justify-content: center;
        .layout-mode-style-aside {
            width: 18%;
            height: 100%;
            background-color: var(--color-sub-3);
        }
        .layout-mode-style-container-box {
            width: 82%;
            height: 100%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--color-sub-1);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 90%;
                background-color: var(--color-sub-4);
            }
        }
    }
    &.streamline {
        display: flex;
        align-items: center;
        justify-content: center;
        .layout-mode-style-container-box {
            width: 100%;
            height: 100%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--color-sub-1);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 90%;
                background-color: var(--color-sub-4);
            }
        }
    }
}
.w80 {
    width: 90%;
}
</style>
