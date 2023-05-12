<template>
    <el-header class="header">
        <el-row justify="center">
            <el-col class="header-row" :span="16" :xs="24">
                <div :class="userInfo.isLogin() ? 'hidden-sm-and-down' : ''" @click="router.push({ name: '/' })" class="header-logo">
                    <img src="~assets/logo.png" />
                    <span class="hidden-xs-only">{{ siteConfig.siteName }}</span>
                </div>
                <div v-if="userInfo.isLogin()" @click="memberCenter.toggleMenuExpand(true)" class="user-menus-expand hidden-md-and-up">
                    <Icon name="fa fa-indent" color="var(--el-color-primary)" size="20" />
                </div>
                <el-menu :default-active="state.activeMenu" class="frontend-header-menu" mode="horizontal" :ellipsis="false">
                    <el-menu-item @click="router.push({ name: '/' })" v-blur index="index">{{ $t('Home') }}</el-menu-item>

                    <template v-for="(item, idx) in siteConfig.headNav" :key="idx">
                        <template v-if="!isEmpty(item.children)">
                            <el-sub-menu v-blur :index="`column-${item.id}`">
                                <template #title>{{ item.title }}</template>
                                <el-menu-item
                                    v-for="(subItem, subIndex) in item.children"
                                    :key="subIndex"
                                    @click="onClickMenu(subItem)"
                                    v-blur
                                    :index="'column-' + subItem.id"
                                >
                                    {{ subItem.title }}
                                </el-menu-item>
                            </el-sub-menu>
                        </template>
                        <template v-else>
                            <el-menu-item @click="onClickMenu(item)" v-blur :index="'column-' + item.id">
                                {{ item.title }}
                            </el-menu-item>
                        </template>
                    </template>

                    <template v-if="memberCenter.state.open">
                        <el-sub-menu v-if="userInfo.isLogin()" v-blur index="user">
                            <template #title>
                                <div class="header-user-box">
                                    <img
                                        class="header-user-avatar"
                                        :src="fullUrl(userInfo.avatar ? userInfo.avatar : '/static/images/avatar.png')"
                                        alt=""
                                    />
                                    {{ userInfo.nickname }}
                                </div>
                            </template>
                            <el-menu-item @click="router.push({ name: 'user' })" v-blur index="user-index">{{ $t('Member Center') }}</el-menu-item>
                            <el-menu-item @click="userInfo.logout()" v-blur index="user-logout">{{ $t('Logout login') }}</el-menu-item>
                        </el-sub-menu>
                        <el-menu-item v-else @click="router.push({ name: 'user' })" v-blur index="user">{{ $t('Member Center') }}</el-menu-item>
                    </template>

                    <el-sub-menu v-blur index="switch-language">
                        <template #title>{{ $t('Language') }}</template>
                        <el-menu-item
                            @click="editDefaultLang(item.name)"
                            v-for="item in config.lang.langArray"
                            :key="item.name"
                            :index="'switch-language-' + item.value"
                            >{{ item.value }}</el-menu-item
                        >
                    </el-sub-menu>
                    <el-menu-item index="theme-switch" class="theme-switch">
                        <DarkSwitch @click="toggleDark()" />
                    </el-menu-item>
                </el-menu>
            </el-col>
        </el-row>
        <el-drawer
            class="aside-drawer"
            :append-to-body="true"
            v-model="memberCenter.state.menuExpand"
            :with-header="false"
            direction="ltr"
            size="70%"
        >
            <Aside />
        </el-drawer>
    </el-header>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'
import { useUserInfo } from '/@/stores/userInfo'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useConfig } from '/@/stores/config'
import { useMemberCenter } from '/@/stores/memberCenter'
import { editDefaultLang } from '/@/lang/index'
import { index } from '/@/api/frontend/index'
import Aside from '/@/layouts/frontend/components/aside.vue'
import DarkSwitch from '/@/layouts/common/components/darkSwitch.vue'
import toggleDark from '/@/utils/useDark'
import { fullUrl } from '/@/utils/common'
import { isEmpty } from 'lodash-es'
import { onClickMenu } from '/@/utils/router'

const state = reactive({
    activeMenu: '',
})

const route = useRoute()
const userInfo = useUserInfo()
const router = useRouter()
const config = useConfig()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()

switch (route.name) {
    case '/':
        state.activeMenu = ''
        break
    case 'userLogin':
        state.activeMenu = 'user'
        break
}

/**
 * 前端初始化请求，获取站点配置信息，动态路由信息等
 */
index()
</script>

<style scoped lang="scss">
.header {
    background-color: var(--ba-bg-color-overlay);
    box-shadow: 0 0 8px rgba(0 0 0 / 8%);
}
.el-header {
    padding: 0;
}
.header-row {
    display: flex;
}
.user-menus-expand {
    display: flex;
    height: 60px;
    align-items: center;
    justify-content: center;
}
.header-logo {
    display: flex;
    height: 60px;
    align-items: center;
    cursor: pointer;
    img {
        height: 34px;
        width: 34px;
    }
    span {
        padding-left: 4px;
        font-size: var(--el-font-size-large);
    }
}
.switch-language {
    display: flex;
    align-items: center;
    span {
        padding-right: 4px;
    }
}
.el-menu--horizontal {
    margin-left: auto;
    border-bottom: none;
}
.header-user-box {
    display: flex;
    align-items: center;
    justify-content: center;
}
.header-user-avatar {
    width: 16px;
    height: 16px;
    margin-right: 4px;
    border-radius: 50%;
}
.el-menu--horizontal > .el-menu-item,
.el-menu--horizontal > :deep(.el-sub-menu) .el-sub-menu__title,
.el-menu--horizontal > .el-menu-item.is-active {
    border-bottom: none;
}
:deep(.aside-drawer) {
    .el-drawer__body {
        padding: 0;
    }
}
@media only screen and (max-width: 768px) {
    .header-logo {
        padding-left: 10px;
    }
    .user-menus-expand {
        padding-left: 20px;
    }
}
@media screen and (max-width: 425px) {
    :deep(.aside-drawer) {
        width: 70% !important;
    }
}
.theme-switch {
    --el-menu-hover-bg-color: none;
}

@at-root .dark {
    .header-logo {
        .hidden-xs-only {
            color: var(--el-text-color-primary);
        }
    }
}
</style>
