<template>
    <el-header class="header">
        <el-row justify="center">
            <el-col class="header-row" :span="16" :xs="24">
                <div @click="router.push({ name: '/' })" class="header-logo">
                    <img src="~assets/logo.png" />
                    <span class="hidden-xs-only">{{ siteConfig.site_name }}</span>
                </div>
                <el-menu :default-active="state.activeMenu" class="frontend-header-menu" mode="horizontal" :ellipsis="false">
                    <el-menu-item @click="router.push({ name: '/' })" v-blur index="index">{{ $t('index.index') }}</el-menu-item>
                    <el-sub-menu v-if="userInfo.id" v-blur @click="router.push({ name: 'user' })" index="user">
                        <template #title>
                            <div class="header-user-box">
                                <img class="header-user-avatar" :src="userInfo.avatar" alt="" />
                                {{ userInfo.nickname }}
                            </div>
                        </template>
                        <el-menu-item @click="router.push({ name: 'user' })" v-blur index="user-index">{{ $t('index.Member Center') }}</el-menu-item>
                        <el-menu-item @click="logout()" v-blur index="user-logout">{{ $t('user.user.Logout login') }}</el-menu-item>
                    </el-sub-menu>
                    <el-menu-item v-else @click="router.push({ name: 'user' })" v-blur index="user">{{ $t('index.Member Center') }}</el-menu-item>
                    <el-sub-menu v-blur index="switch-language">
                        <template #title>{{ $t('index.language') }}</template>
                        <el-menu-item
                            @click="editDefaultLang(item.name)"
                            v-for="item in config.lang.langArray"
                            :key="item.name"
                            :index="'switch-language-' + item.value"
                            >{{ item.value }}</el-menu-item
                        >
                    </el-sub-menu>
                </el-menu>
            </el-col>
        </el-row>
    </el-header>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'
import { useUserInfo } from '/@/stores/userInfo'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useConfig } from '/@/stores/config'
import { editDefaultLang } from '/@/lang/index'
import { postLogout } from '/@/api/frontend/user/index'
import 'element-plus/theme-chalk/display.css'
import { Local } from '/@/utils/storage'
import { USER_INFO } from '/@/stores/constant/cacheKey'

const state = reactive({
    activeMenu: '',
})

const route = useRoute()
const userInfo = useUserInfo()
const router = useRouter()
const config = useConfig()
const siteConfig = useSiteConfig()

switch (route.name) {
    case '/':
        state.activeMenu = ''
        break
    case 'userLogin':
        state.activeMenu = 'user'
        break
}

const logout = () => {
    postLogout().then((res) => {
        if (res.code == 1) {
            Local.remove(USER_INFO)
            router.go(0)
        }
    })
}
</script>

<style scoped lang="scss">
.header {
    background-color: #fff;
    box-shadow: 0px 0px 8px rgba(0 0 0 / 8%);
}
.el-header {
    padding: 0;
}
.header-row {
    display: flex;
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
}
.el-menu--horizontal > .el-menu-item,
.el-menu--horizontal > :deep(.el-sub-menu) .el-sub-menu__title,
.el-menu--horizontal > .el-menu-item.is-active {
    border-bottom: none;
}
@media only screen and (max-width: 768px) {
    .header-logo {
        padding-left: 10px;
    }
}
</style>
