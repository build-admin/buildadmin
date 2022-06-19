<template>
    <div>
        <Header />
        <el-container class="container">
            <el-main class="main">
                <div class="main-container">
                    <div class="main-left">
                        <div class="main-title">{{ siteConfig.site_name }}</div>
                        <div class="main-content">
                            {{ $t('index.Steve Jobs') }}
                        </div>
                        <el-button @click="$router.push('/user')" color="#FFFFFF" size="large">{{ $t('index.Member Center') }}</el-button>
                    </div>
                    <div class="main-right">
                        <img :src="indexCover" alt="" />
                    </div>
                </div>
            </el-main>
        </el-container>
        <Footer />
    </div>
</template>

<script setup lang="ts">
import indexCover from '/@/assets/index/index-cover.svg'
import { index } from '/@/api/frontend/index'
import { setTitle } from '/@/utils/common'
import { useSiteConfig } from '/@/stores/siteConfig'
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'

const siteConfig = useSiteConfig()

index().then((res) => {
    setTitle(res.data.site.site_name)
    siteConfig.$state = res.data.site
})
</script>

<style scoped lang="scss">
.container {
    width: 100vw;
    height: 100vh;
    background: url(/@/assets/bg.jpg) repeat;
    color: var(--color-basic-white);
    .main {
        height: calc(100vh - 120px);
        padding: 0;
        .main-container {
            display: flex;
            height: 100%;
            width: 64%;
            margin: 0 auto;
            align-items: center;
            justify-content: space-between;
            .main-left {
                padding-right: 50px;
                .main-title {
                    font-size: 45px;
                }
                .main-content {
                    padding-top: 20px;
                    padding-bottom: 40px;
                    font-size: var(--el-font-size-large);
                }
            }
            .main-right {
                img {
                    width: 380px;
                }
            }
        }
    }
}
.header {
    background-color: transparent;
    box-shadow: none;
    position: fixed;
    width: 100%;
    :deep(.header-logo) {
        span {
            padding-left: 4px;
            color: var(--color-basic-white);
        }
    }
    :deep(.frontend-header-menu) {
        background: transparent;
        .el-menu-item,
        .el-sub-menu .el-sub-menu__title {
            color: var(--color-basic-white);
            &.is-active {
                color: var(--color-basic-white) !important;
            }
            &:hover {
                background-color: transparent;
                color: var(--el-menu-hover-text-color);
            }
        }
    }
}
.footer {
    color: var(--color-secondary);
    background-color: transparent;
}

@media screen and (max-width: 1024px) {
    .main-container {
        width: 90% !important;
        flex-wrap: wrap;
        align-content: center;
        justify-content: center !important;
        .main-right {
            padding-top: 50px;
        }
    }
}
@media screen and (max-width: 375px) {
    .main-right img {
        width: 300px !important;
    }
}
</style>
