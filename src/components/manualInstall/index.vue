<template>
    <div class="container">
        <div class="title">{{ t('Unfinished matters manually') }}</div>
        <div class="content">
            <div class="content-item">1、{{ t('Open terminal (windows PowerShell)') }}</div>
            <div class="content-item">
                <div>2、{{ t('Execute command') }}</div>
                <div class="command">cd {{ state.webPath }}</div>
            </div>
            <div class="content-item">
                <div>3、{{ t('Execute command') }}</div>
                <div class="command">npm install</div>
                <div @click="goUrl('https://www.kancloud.cn/buildadmin/buildadmin/2653900')" class="block-help link">
                    {{ t('Execution failed?') }}
                </div>
            </div>
            <div class="content-item">
                <div>4、{{ t('Execute command') }}</div>
                <div class="command">npm run build:online</div>
                <div @click="goUrl('https://www.kancloud.cn/buildadmin/buildadmin/2655209')" class="block-help link">
                    {{ t('Execution failed?') }}
                </div>
            </div>
            <div class="content-item">
                <div>5、{{ t('Move the built file to the specified location of the system') }}</div>
                <div @click="mvDist" class="block-help link size-15">{{ t('Click to try to automatically move the build file') }}</div>
                <div class="step-box">
                    <div class="step">
                        1. {{ t('The build output directory is: site') }}<span class="text-bold">{{ t('root directory / dist') }}</span>
                    </div>
                    <div class="step">
                        2. {{ t('Please move 1') }}<span class="text-bold">assets</span>{{ t('Please move 2')
                        }}<span class="text-bold">index.html</span>{{ t('Please move 3') }}<span class="text-bold">public</span
                        >{{ t('Please move 4') }}
                    </div>
                    <div class="step">3. {{ t('You can delete the build output directory directly') }}</div>
                </div>
                <div class="min-help">
                    {{
                        t(
                            'During construction, all files in the output directory will be overwritten, so the system is designed to build in the root directory first, and then move to the public directory to prevent other files in the public from being overwritten'
                        )
                    }}
                </div>
            </div>
            <div v-if="state.showLoading" class="loading">{{ state.showLoading }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { getManualInstall, postMvDist } from '/@/api/install/index'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { useCommon } from '/@/stores/common'

const { t } = useI18n()
const common = useCommon()

const state = reactive({
    showLoading: '',
    webPath: t('Getting full path of root directory / Web'),
})

const goUrl = (url: string) => {
    window.open(url)
}

const mvDist = () => {
    state.showLoading = t('Moving automatically')
    postMvDist()
        .then((res) => {
            if (res.data.code == 1) {
                common.setStep('done')
            } else {
                ElMessage({
                    type: 'error',
                    message: res.data.msg,
                    center: true,
                })
            }
        })
        .finally(() => {
            state.showLoading = ''
        })
}

getManualInstall().then((res) => {
    if (res.data.code == 1) {
        state.webPath = res.data.data.webPath
    } else {
        ElMessage({
            type: 'error',
            message: res.data.msg,
            center: true,
        })
    }
})
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
        }
        .content-item:last-child {
            margin-bottom: 0;
        }
    }
}
:deep(.text-bold) {
    font-weight: bold;
    padding: 0 2px;
}
.loading {
    font-size: 13px;
    color: #909399;
    text-align: right;
}
</style>
