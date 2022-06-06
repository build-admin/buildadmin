<template>
    <div class="layout-config-drawer">
        <el-drawer v-model="configStore.layout.showDrawer" :title="t('layouts.Layout configuration')" size="310px" @close="onCloseDrawer">
            <el-scrollbar class="layout-mode-style-scrollbar">
                <el-form ref="formRef" :model="configStore.layout">
                    <div class="layout-mode-styles-box">
                        <el-divider border-style="dashed">{{ t('layouts.Layout mode') }}</el-divider>
                        <div class="layout-mode-box-style">
                            <el-row class="layout-mode-box-style-row" :gutter="10">
                                <el-col :span="12">
                                    <div
                                        @click="setLayoutMode('Default')"
                                        class="layout-mode-style default"
                                        :class="configStore.layout.layoutMode == 'Default' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-aside"></div>
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">{{ t('layouts.default') }}</div>
                                    </div>
                                </el-col>
                                <el-col :span="12">
                                    <div
                                        @click="setLayoutMode('Classic')"
                                        class="layout-mode-style classic"
                                        :class="configStore.layout.layoutMode == 'Classic' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-aside"></div>
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">{{ t('layouts.classic') }}</div>
                                    </div>
                                </el-col>
                            </el-row>
                            <el-row :gutter="10">
                                <el-col :span="12">
                                    <div
                                        @click="setLayoutMode('Streamline')"
                                        class="layout-mode-style streamline"
                                        :class="configStore.layout.layoutMode == 'Streamline' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">{{ t('layouts.Single column') }}</div>
                                    </div>
                                </el-col>
                            </el-row>
                        </div>
                        <el-divider border-style="dashed">{{ t('layouts.overall situation') }}</el-divider>
                        <div class="layout-config-global">
                            <el-form-item :label="t('layouts.Background page switching animation')">
                                <el-select
                                    @change="onCommitState($event, 'mainAnimation')"
                                    v-model="configStore.layout.mainAnimation"
                                    :placeholder="t('layouts.Please select an animation name')"
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

                        <el-divider border-style="dashed">{{ t('layouts.sidebar') }}</el-divider>
                        <div class="layout-config-aside">
                            <el-form-item :label="t('layouts.Side menu bar background color')">
                                <el-color-picker @change="onCommitState($event, 'menuBackground')" v-model="configStore.layout.menuBackground" />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu text color')">
                                <el-color-picker @change="onCommitState($event, 'menuColor')" v-model="configStore.layout.menuColor" />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu active item background color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'menuActiveBackground')"
                                    v-model="configStore.layout.menuActiveBackground"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu active item text color')">
                                <el-color-picker @change="onCommitState($event, 'menuActiveColor')" v-model="configStore.layout.menuActiveColor" />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Show side menu top bar (logo bar)')">
                                <el-switch @change="onCommitState($event, 'menuShowTopBar')" v-model="configStore.layout.menuShowTopBar"></el-switch>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu top bar background color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'menuTopBarBackground')"
                                    v-model="configStore.layout.menuTopBarBackground"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu width (when expanded)')">
                                <el-input @input="onCommitState($event, 'menuWidth')" type="number" :step="10" v-model="configStore.layout.menuWidth">
                                    <template #append>px</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu default icon')">
                                <selector @change="onCommitMenuDefaultIcon($event, 'menuDefaultIcon')" v-model="configStore.layout.menuDefaultIcon" />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu horizontal collapse')">
                                <el-switch @change="onCommitState($event, 'menuCollapse')" v-model="configStore.layout.menuCollapse"></el-switch>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu accordion')">
                                <el-switch
                                    @change="onCommitState($event, 'menuUniqueOpened')"
                                    v-model="configStore.layout.menuUniqueOpened"
                                ></el-switch>
                            </el-form-item>
                        </div>

                        <el-divider border-style="dashed">{{ t('layouts.Top bar') }}</el-divider>
                        <div class="layout-config-aside">
                            <el-form-item :label="t('layouts.Top bar background color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarBackground')"
                                    v-model="configStore.layout.headerBarBackground"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar text color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarTabColor')"
                                    v-model="configStore.layout.headerBarTabColor"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Background color when hovering over the top bar')">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarHoverBackground')"
                                    v-model="configStore.layout.headerBarHoverBackground"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar menu active item background color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarTabActiveBackground')"
                                    v-model="configStore.layout.headerBarTabActiveBackground"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar menu active item text color')">
                                <el-color-picker
                                    @change="onCommitState($event, 'headerBarTabActiveColor')"
                                    v-model="configStore.layout.headerBarTabActiveColor"
                                />
                            </el-form-item>
                        </div>
                        <el-popconfirm
                            @confirm="restoreDefault"
                            :title="t('layouts.Are you sure you want to restore all configurations to the default values?')"
                        >
                            <template #reference>
                                <div class="ba-center">
                                    <el-button class="w80" type="info">{{ t('layouts.Restore default') }}</el-button>
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
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useRouter } from 'vue-router'
import selector from '/@/components/icon/selector.vue'
import { STORE_CONFIG, BEFORE_RESIZE_LAYOUT } from '/@/stores/constant/cacheKey'
import { Local, Session } from '/@/utils/storage'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const configStore = useConfig()
const navTabs = useNavTabs()
const router = useRouter()

const onCommitState = (value: any, name: any) => {
    configStore.setLayout(name, value)
}

const setLayoutMode = (mode: string) => {
    Session.set(BEFORE_RESIZE_LAYOUT, {
        layoutMode: mode,
        menuCollapse: configStore.layout.menuCollapse,
    })
    configStore.setLayoutMode(mode)
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

const restoreDefault = () => {
    Local.remove(STORE_CONFIG)
    Session.remove(BEFORE_RESIZE_LAYOUT)
    router.go(0)
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
