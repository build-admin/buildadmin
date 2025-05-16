<template>
    <div class="layout-config-drawer">
        <el-drawer :model-value="configStore.layout.showDrawer" :title="t('layouts.Layout configuration')" size="310px" @close="onCloseDrawer">
            <el-scrollbar class="layout-mode-style-scrollbar">
                <el-form :model="configStore.layout">
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
                                <el-col :span="12">
                                    <div
                                        @click="setLayoutMode('Double')"
                                        class="layout-mode-style double"
                                        :class="configStore.layout.layoutMode == 'Double' ? 'active' : ''"
                                    >
                                        <div class="layout-mode-style-box">
                                            <div class="layout-mode-style-aside"></div>
                                            <div class="layout-mode-style-container-box">
                                                <div class="layout-mode-style-header"></div>
                                                <div class="layout-mode-style-container"></div>
                                            </div>
                                        </div>
                                        <div class="layout-mode-style-name">{{ t('layouts.Double column') }}</div>
                                    </div>
                                </el-col>
                            </el-row>
                        </div>
                        <el-divider border-style="dashed">{{ t('layouts.overall situation') }}</el-divider>
                        <div class="layout-config-global">
                            <el-form-item size="large" :label="t('layouts.Dark mode')">
                                <DarkSwitch @click="toggleDark()" />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Background page switching animation')">
                                <el-select
                                    @change="onCommitState($event, 'mainAnimation')"
                                    :model-value="configStore.layout.mainAnimation"
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
                                <el-color-picker
                                    @change="onCommitColorState($event, 'menuBackground')"
                                    :model-value="configStore.getColorVal('menuBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu text color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'menuColor')"
                                    :model-value="configStore.getColorVal('menuColor')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu active item background color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'menuActiveBackground')"
                                    :model-value="configStore.getColorVal('menuActiveBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu active item text color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'menuActiveColor')"
                                    :model-value="configStore.getColorVal('menuActiveColor')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Show side menu top bar (logo bar)')">
                                <el-switch
                                    @change="onCommitState($event, 'menuShowTopBar')"
                                    :model-value="configStore.layout.menuShowTopBar"
                                ></el-switch>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu top bar background color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'menuTopBarBackground')"
                                    :model-value="configStore.getColorVal('menuTopBarBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu width (when expanded)')">
                                <el-input
                                    @input="onCommitState($event, 'menuWidth')"
                                    type="number"
                                    :step="10"
                                    :model-value="configStore.layout.menuWidth"
                                >
                                    <template #append>px</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu default icon')">
                                <IconSelector
                                    @change="onCommitMenuDefaultIcon($event, 'menuDefaultIcon')"
                                    :model-value="configStore.layout.menuDefaultIcon"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu horizontal collapse')">
                                <el-switch @change="onCommitState($event, 'menuCollapse')" :model-value="configStore.layout.menuCollapse"></el-switch>
                            </el-form-item>
                            <el-form-item :label="t('layouts.Side menu accordion')">
                                <el-switch
                                    @change="onCommitState($event, 'menuUniqueOpened')"
                                    :model-value="configStore.layout.menuUniqueOpened"
                                ></el-switch>
                            </el-form-item>
                        </div>

                        <el-divider border-style="dashed">{{ t('layouts.Top bar') }}</el-divider>
                        <div class="layout-config-aside">
                            <el-form-item :label="t('layouts.Top bar background color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'headerBarBackground')"
                                    :model-value="configStore.getColorVal('headerBarBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar text color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'headerBarTabColor')"
                                    :model-value="configStore.getColorVal('headerBarTabColor')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Background color when hovering over the top bar')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'headerBarHoverBackground')"
                                    :model-value="configStore.getColorVal('headerBarHoverBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar menu active item background color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'headerBarTabActiveBackground')"
                                    :model-value="configStore.getColorVal('headerBarTabActiveBackground')"
                                />
                            </el-form-item>
                            <el-form-item :label="t('layouts.Top bar menu active item text color')">
                                <el-color-picker
                                    @change="onCommitColorState($event, 'headerBarTabActiveColor')"
                                    :model-value="configStore.getColorVal('headerBarTabActiveColor')"
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
import { nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import IconSelector from '/@/components/baInput/components/iconSelector.vue'
import DarkSwitch from '/@/layouts/common/components/darkSwitch.vue'
import { useConfig } from '/@/stores/config'
import { BEFORE_RESIZE_LAYOUT, STORE_CONFIG } from '/@/stores/constant/cacheKey'
import type { Layout } from '/@/stores/interface'
import { useNavTabs } from '/@/stores/navTabs'
import { Local, Session } from '/@/utils/storage'
import toggleDark from '/@/utils/useDark'

const { t } = useI18n()
const configStore = useConfig()
const navTabs = useNavTabs()
const router = useRouter()

const onCommitState = (value: any, name: any) => {
    configStore.setLayout(name, value)
}

const onCommitColorState = (value: string | null, name: keyof Layout) => {
    if (value === null) return
    const colors = configStore.layout[name] as string[]
    if (configStore.layout.isDark) {
        colors[1] = value
    } else {
        colors[0] = value
    }
    configStore.setLayout(name, colors)
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
    nextTick(() => {
        navTabs.setTabsViewRoutes(menus)
    })
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
    border: 1px solid var(--el-border-color-light);
    border-radius: var(--el-border-radius-small);
    &:hover,
    &.active {
        border: 1px solid var(--el-color-primary);
    }
    .layout-mode-style-name {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--el-color-primary-light-5);
        border-radius: 50%;
        height: 50px;
        width: 50px;
        border: 1px solid var(--el-color-primary-light-3);
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
            background-color: var(--el-border-color-lighter);
        }
        .layout-mode-style-container-box {
            width: 68%;
            height: 90%;
            margin-left: 4%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--el-border-color-lighter);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 85%;
                background-color: var(--el-border-color-extra-light);
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
            background-color: var(--el-border-color-lighter);
        }
        .layout-mode-style-container-box {
            width: 82%;
            height: 100%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--el-border-color);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 90%;
                background-color: var(--el-border-color-extra-light);
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
                background-color: var(--el-border-color);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 90%;
                background-color: var(--el-border-color-extra-light);
            }
        }
    }
    &.double {
        display: flex;
        align-items: center;
        justify-content: center;
        .layout-mode-style-aside {
            width: 18%;
            height: 100%;
            background-color: var(--el-border-color);
        }
        .layout-mode-style-container-box {
            width: 82%;
            height: 100%;
            .layout-mode-style-header {
                width: 100%;
                height: 10%;
                background-color: var(--el-border-color);
            }
            .layout-mode-style-container {
                width: 100%;
                height: 90%;
                background-color: var(--el-border-color-extra-light);
            }
        }
    }
}
.w80 {
    width: 90%;
}
</style>
