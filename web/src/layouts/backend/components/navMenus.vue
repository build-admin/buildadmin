<template>
    <div class="nav-menus" :class="layoutConfig.layoutMode">
        <router-link class="h100" target="_blank" :title="t('home')" to="/">
            <div class="nav-menu-item">
                <Icon :color="layoutConfig.headerBarTabColor" class="nav-menu-icon" name="el-icon-Monitor" size="18" />
            </div>
        </router-link>
        <el-dropdown
            @visible-change="onCurrentNavMenu($event, 'lang')"
            class="h100"
            size="large"
            :hide-timeout="50"
            placement="bottom"
            trigger="click"
            :hide-on-click="true"
        >
            <div class="nav-menu-item" :class="state.currentNavMenu == 'lang' ? 'hover' : ''">
                <Icon :color="layoutConfig.headerBarTabColor" class="nav-menu-icon" name="local-lang" size="18" />
            </div>
            <template #dropdown>
                <el-dropdown-menu class="chang-lang">
                    <el-dropdown-item v-for="item in langArray" :key="item.name" @click="editDefaultLang(item.name)">
                        {{ item.value }}
                    </el-dropdown-item>
                </el-dropdown-menu>
            </template>
        </el-dropdown>
        <div @click="onFullScreen" class="nav-menu-item" :class="state.isFullScreen ? 'hover' : ''">
            <Icon :color="layoutConfig.headerBarTabColor" class="nav-menu-icon" v-if="state.isFullScreen" name="local-full-screen-cancel" size="18" />
            <Icon :color="layoutConfig.headerBarTabColor" class="nav-menu-icon" v-else name="el-icon-FullScreen" size="18" />
        </div>
        <el-popover
            @show="onCurrentNavMenu(true, 'adminInfo')"
            @hide="onCurrentNavMenu(false, 'adminInfo')"
            placement="bottom-end"
            :hide-after="0"
            :width="260"
            trigger="click"
            popper-class="admin-info-box"
        >
            <template #reference>
                <div class="admin-info" :class="state.currentNavMenu == 'adminInfo' ? 'hover' : ''">
                    <el-avatar :size="25" fit="fill">
                        <img src="~assets/avatar.png" alt="" />
                    </el-avatar>
                    <div class="admin-name">Admin</div>
                </div>
            </template>
            <div>
                <div class="admin-info-base">
                    <el-avatar :size="70" fit="fill">
                        <img src="~assets/avatar.png" alt="" />
                    </el-avatar>
                    <div class="admin-info-other">
                        <div class="admin-info-name">Admin</div>
                        <div class="admin-info-lasttime">2022-01-23 18:52</div>
                    </div>
                </div>
                <div class="admin-info-footer">
                    <el-button type="primary" plain>个人资料</el-button>
                    <el-button type="danger" plain>注销</el-button>
                </div>
            </div>
        </el-popover>
        <div @click="onSetState('showDrawer', true)" class="nav-menu-item">
            <Icon :color="layoutConfig.headerBarTabColor" class="nav-menu-icon" name="fa fa-cogs" size="18" />
        </div>
        <Config />
    </div>
</template>

<script lang="ts" setup>
import { computed, reactive } from 'vue'
import { editDefaultLang } from '/@/lang'
import screenfull from 'screenfull'
import { store } from '/@/store/index'
import { ElMessage } from 'element-plus'
import { useI18n } from 'vue-i18n'
import Config from './config.vue'

const { t } = useI18n()

const state = reactive({
    isFullScreen: false,
    currentNavMenu: '',
    showLayoutDrawer: false,
})

const langArray = computed(() => store.state.config.langArray)
const layoutConfig = computed(() => store.state.config.layout)

const onCurrentNavMenu = (status: boolean, name: string) => {
    state.currentNavMenu = status ? name : ''
}

const onFullScreen = () => {
    if (!screenfull.isEnabled) {
        ElMessage.warning(t('layout.Full screen is not supported'))
        return false
    }
    screenfull.toggle()
    screenfull.onchange(() => {
        state.isFullScreen = screenfull.isFullscreen
    })
}

const onSetState = (name: string, value: any) => {
    store.commit('config/set', {
        name: 'layout.' + name,
        value: value,
    })
}
</script>

<style scoped lang="scss">
.nav-menus.Default {
    border-radius: var(--el-border-radius-base);
    box-shadow: var(--el-box-shadow-light);
}
.nav-menus {
    display: flex;
    align-items: center;
    height: 100%;
    margin-left: auto;
    background-color: v-bind('layoutConfig.headerBarBackground');
    .nav-menu-item {
        height: 100%;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        .nav-menu-icon {
            box-sizing: content-box;
        }
        &:hover {
            .icon {
                animation: twinkle 0.3s ease-in-out;
            }
        }
    }
    .admin-info {
        display: flex;
        height: 100%;
        padding: 0 10px;
        align-items: center;
        cursor: pointer;
        user-select: none;
        color: v-bind('layoutConfig.headerBarTabColor');
    }
    .admin-name {
        padding-left: 6px;
    }
    .nav-menu-item:hover,
    .admin-info:hover,
    .nav-menu-item.hover,
    .admin-info.hover {
        background: v-bind('layoutConfig.headerBarHoverBackground');
    }
}
.chang-lang :deep(.el-dropdown-menu__item) {
    justify-content: center;
}
.admin-info-base {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding-top: 10px;
    .admin-info-other {
        display: block;
        width: 100%;
        text-align: center;
        padding: 10px 0;
        .admin-info-name {
            font-size: var(--el-font-size-large);
        }
    }
}
.admin-info-footer {
    padding: 10px 0;
    margin: 0 -12px -12px -12px;
    display: flex;
    justify-content: space-around;
    background: var(--color-bg-2);
}

@keyframes twinkle {
    0% {
        transform: scale(0);
    }
    80% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}
</style>
