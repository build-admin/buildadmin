<template>
    <el-header class="header">
        <el-row justify="center">
            <el-col class="header-row" :span="16" :xs="24">
                <div @click="router.push({ name: '/' })" class="header-logo">
                    <img src="~assets/logo.png" />
                    <span class="hidden-xs-only">{{ siteConfig.site_name }}</span>
                </div>
                <el-menu :default-active="state.activeMenu" class="frontend-header-menu" mode="horizontal" :ellipsis="false">
                    <el-menu-item v-blur index="index">{{ $t('index.index') }}</el-menu-item>
                    <el-menu-item v-blur index="user">{{ $t('index.Member Center') }}</el-menu-item>
                </el-menu>
            </el-col>
        </el-row>
    </el-header>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useSiteConfig } from '/@/stores/siteConfig'
import 'element-plus/theme-chalk/display.css'

const state = reactive({
    activeMenu: 'user',
})

const router = useRouter()
const siteConfig = useSiteConfig()
</script>

<style scoped lang="scss">
.header {
    background-color: #fff;
    box-shadow: 0px 0px 8px rgba(0 0 0 / 8%);
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
.el-menu--horizontal {
    margin-left: auto;
    border-bottom: none;
}
.el-menu--horizontal > .el-menu-item,
.el-menu--horizontal > .el-menu-item.is-active {
    border-bottom: none;
}
</style>
