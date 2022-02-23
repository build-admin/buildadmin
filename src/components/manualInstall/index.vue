<template>
    <div class="container">
        <div class="title">手动完成未尽事宜</div>
        <transition name="slide-bottom">
            <div v-show="state.showError" class="error-tips">
                <div>{{ state.showError }}</div>
                <img @click="closeError" class="error-img" src="~assets/img/install/close.png" alt="" />
            </div>
        </transition>
        <div class="content">
            <div class="content-item">1、打开终端（Windows PowerShell）</div>
            <div class="content-item">
                <div>2、执行命令</div>
                <div class="command">cd {{ state.webPath }}</div>
            </div>
            <div class="content-item">
                <div>3、执行命令</div>
                <div class="command">cnpm install</div>
                <div @click="goUrl('https://www.kancloud.cn/buildadmin/buildadmin/2653900')" class="block-help link">执行失败了?</div>
            </div>
            <div class="content-item">
                <div>4、执行命令</div>
                <div class="command">cnpm run build:online</div>
                <div @click="goUrl('https://www.kancloud.cn/buildadmin/buildadmin/2655209')" class="block-help link">执行失败了?</div>
            </div>
            <div class="content-item">
                <div>5、移动构建好的文件到系统指定位置</div>
                <div @click="mvDist" class="block-help link size-15">点击尝试自动移动构建文件</div>
                <div class="step-box">
                    <div class="step">1. 构建输出目录为：站点<span class="text-bold">根目录/dist</span></div>
                    <div class="step">
                        2. 请移动构建输出目录中的<span class="text-bold">assets</span>文件夹和<span class="text-bold">index.html</span
                        >文件到根目录的<span class="text-bold">public</span>目录下
                    </div>
                    <div class="step">3. 您可以直接删除构建输出目录</div>
                </div>
                <div class="min-help">
                    构建时，会覆盖输出目录的所有文件，所以系统设计为先在根目录构建，然后移动到public目录，以免public内的其他文件被覆盖掉
                </div>
            </div>
            <div v-if="state.showLoading" class="loading">{{ state.showLoading }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { manualInstallUrl, mvDistUrl } from '/@/api/install/index'
import { Axios, errorTips, Response } from '/@/utils/axios'
import { global } from '/@/utils/globalVar'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const state = reactive({
    showError: '',
    showLoading: '',
    webPath: '正在获取 根目录/web 的完整路径',
})

const goUrl = (url: string) => {
    window.open(url)
}

const closeError = () => {
    state.showError = ''
}

Axios.get(manualInstallUrl)
    .then((res) => {
        if (res.data.code == 1) {
            state.webPath = res.data.data.webPath
        } else {
            state.showError = res.data.msg
        }
    })
    .catch((err) => {
        state.showError = t(errorTips(err))
    })

const mvDist = () => {
    state.showLoading = '正在自动移动...'
    Axios.post(mvDistUrl)
        .then((res) => {
            if (res.data.code == 1) {
                global.step = 'done'
            } else {
                state.showError = res.data.msg
            }
            state.showLoading = ''
        })
        .catch((err) => {
            state.showLoading = ''
            state.showError = t(errorTips(err))
        })
}
</script>

<style scoped lang="scss">
.container {
    padding: 20px;
    .title {
        display: block;
        text-align: center;
        font-size: 20px;
        color: #303133;
    }
    .content {
        display: block;
        max-width: 500px;
        padding: 15px;
        margin: 15px auto;
        background-color: #fff;
        border-radius: 4px;
        font-size: 15px;
        .content-item {
            line-height: 1.3;
            border-radius: 4px;
            padding: 10px;
            background-color: #f5f5f5;
            word-break: break-all;
            font-family: Consolas, Monaco, Andale Mono, Ubuntu Mono, monospace;
            margin-bottom: 15px;
            .command {
                line-height: 2;
                font-weight: bold;
            }
            .block-help {
                display: inline-block;
                line-height: 2;
                font-size: 13px;
                color: #303133;
            }
            .block-help.link {
                color: #3f6ad8;
                cursor: pointer;
            }
            .min-help {
                color: #909399;
                font-size: 12px;
            }
            .size-15 {
                font-size: 15px;
            }
            .step-box {
                padding-bottom: 10px;
                .step {
                    font-size: 14px;
                    line-height: 1.5;
                }
            }
            .text-bold {
                font-weight: bold;
                padding: 0 2px;
            }
        }
        .content-item:last-child {
            margin-bottom: 0;
        }
    }
}
.error-tips {
    display: block;
    position: relative;
    max-width: 500px;
    padding: 15px;
    margin: 15px auto;
    background-color: #f56c6c;
    color: #fff;
    border-radius: 4px;
    font-size: 15px;
    .error-img {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 22px;
        height: 22px;
        cursor: pointer;
    }
}
.loading {
    font-size: 13px;
    color: #909399;
    text-align: right;
}
</style>
