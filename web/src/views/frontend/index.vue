<template>
    <div class="container">
        <el-container>
            <el-header class="header">
                <div class="header-logo">
                    <img src="~assets/logo.png" />
                    <span>{{ state.data.site_name }}</span>
                </div>
                <!-- <div class="header-link">
                    <Icon name="el-icon-User" size="16" color="#ffffff" />
                    <span>会员中心</span>
                </div> -->
            </el-header>
            <el-main class="main">
                <div class="main-container">
                    <div class="main-left">
                        <div class="main-title">{{ state.data.site_name }}</div>
                        <div class="main-content">伟大的艺术品不必追随潮流，他本身就能引领潮流。 -- 乔布斯</div>
                    </div>
                    <div class="main-right">
                        <img :src="indexCover" alt="" />
                    </div>
                </div>
            </el-main>
            <el-footer class="footer">
                <div>
                    Copyright @ 2020~2022 {{ state.data.site_name }} 版权所有 <a href="http://beian.miit.gov.cn/">{{ state.data.record_number }}</a>
                </div>
            </el-footer>
        </el-container>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import indexCover from '/@/assets/index/index-cover.svg'
import { index } from '/@/api/frontend/index'

const state: {
    data: {
        site_name?: string
        record_number?: string
    }
} = reactive({
    data: {},
})

index().then((res) => {
    state.data = res.data
})
</script>

<style scoped lang="scss">
.container {
    width: 100vw;
    height: 100vh;
    background: url(/@/assets/bg.jpg) repeat;
    color: var(--color-basic-white);
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 60%;
        margin: 0 auto;
        padding: 0;
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
                font-size: var(--el-font-size-extra-large);
                font-weight: bold;
                &:hover {
                    color: var(--color-info-sub-1);
                }
            }
        }
        .header-link {
            display: flex;
            align-items: center;
            height: var(--el-header-height);
            font-size: var(--el-font-size-medium);
            color: var(--color-basic-white);
            cursor: pointer;
            span {
                padding-left: 2px;
            }
            &:hover {
                color: var(--color-info-sub-1);
            }
        }
    }
    .main {
        height: calc(100vh - 120px);
        padding: 0;
        .main-container {
            display: flex;
            height: 100%;
            width: 60%;
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
    .footer {
        width: 60%;
        margin: 0 auto;
        padding: 0;
        color: var(--color-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        a {
            color: var(--color-secondary);
        }
    }
}

@media screen and (max-width: 1024px) {
    .header {
        width: 90% !important;
    }
    .main-container {
        width: 90% !important;
        flex-wrap: wrap;
        align-content: center;
        justify-content: center !important;
        .main-right {
            padding-top: 50px;
        }
    }
    .footer {
        width: 90% !important;
    }
}
@media screen and (max-width: 375px) {
    .main-right img {
        width: 300px !important;
    }
}
</style>
