<template>
    <el-header class="header">
        <el-row justify="center">
            <el-col class="header-row" :xs="24" :sm="24" :md="16">
                <div @click="router.push({ name: '/' })" class="header-logo">
                    <img src="~assets/logo.png" />
                    <span class="site-name">{{ siteConfig.siteName }}</span>
                </div>
                <div v-if="!memberCenter.state.menuExpand" @click="memberCenter.toggleMenuExpand(true)" class="user-menus-expand hidden-md-and-up">
                    <Icon name="fa fa-indent" color="var(--el-color-primary)" size="20" />
                </div>

                <el-scrollbar class="hidden-sm-and-down">
                    <Menu class="frontend-header-menu" :ellipsis="false" mode="horizontal" />
                </el-scrollbar>
            </el-col>
        </el-row>
        <el-drawer
            class="ba-aside-drawer"
            :append-to-body="true"
            v-model="memberCenter.state.menuExpand"
            :with-header="false"
            direction="ltr"
            size="40%"
        >
            <div class="header-row">
                <div @click="router.push({ name: '/' })" class="header-logo">
                    <img src="~assets/logo.png" />
                    <span class="site-name">{{ siteConfig.siteName }}</span>
                </div>
                <div @click="memberCenter.toggleMenuExpand(false)" class="user-menus-expand hidden-md-and-up">
                    <Icon name="fa fa-dedent" color="var(--el-color-primary)" size="20" />
                </div>
            </div>
            <Menu :show-icon="true" mode="vertical" />
        </el-drawer>
    </el-header>
</template>

<script setup lang="ts">
import { watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { initialize } from '/@/api/frontend/index'
import Menu from '/@/layouts/frontend/components/menu.vue'

const route = useRoute()
const router = useRouter()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()

watch(
    () => route.path,
    () => {
        memberCenter.toggleMenuExpand(false)
    },
    {
        immediate: true,
    }
)

/**
 * 前端初始化请求，获取站点配置信息，动态路由信息等
 */
initialize()
</script>

<style scoped lang="scss">
.header {
    background-color: var(--ba-bg-color-overlay);
    box-shadow: 0 0 8px rgba(0 0 0 / 8%);
    .frontend-header-menu {
        height: var(--el-header-height);
    }
}
.header-row {
    display: flex;
    justify-content: space-between;
    .header-logo {
        display: flex;
        height: var(--el-header-height);
        align-items: center;
        padding-right: 15px;
        cursor: pointer;
        img {
            height: 34px;
            width: 34px;
        }
        .site-name {
            padding-left: 4px;
            font-size: var(--el-font-size-large);
            white-space: nowrap;
        }
    }
    .user-menus-expand {
        display: flex;
        height: var(--el-header-height);
        align-items: center;
        justify-content: center;
    }
}
.ba-aside-drawer {
    .header-row {
        padding: 10px 20px;
        background-color: var(--el-color-info-light-9);
        .header-logo {
            img {
                height: 28px;
                width: 28px;
            }
        }
    }
}

@at-root html.dark {
    .header-logo .site-name {
        color: var(--el-text-color-primary);
    }
}
@media screen and (max-width: 768px) {
    .user-menus-expand {
        padding: 0;
    }
}
@media screen and (max-width: 414px) {
    .frontend-header-menu :deep(.el-sub-menu .el-sub-menu__title) {
        padding: 0 20px;
        .el-icon {
            display: none;
        }
    }
}
</style>
