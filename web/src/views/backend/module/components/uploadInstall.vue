<template>
    <div class="upload-install">
        <div class="tips">
            <div class="title">{{ $t('module.Local upload warning') }}</div>
            <div class="tip-item">1. {{ $t('module.The module can modify and add system files') }}</div>
            <div class="tip-item">2. {{ $t('module.The module can execute sql commands and codes') }}</div>
            <div class="tip-item">3. {{ $t('module.The module can install new front and rear dependencies') }}</div>
        </div>
        <el-upload class="upload-module" :show-file-list="false" accept=".zip" drag :auto-upload="false" @change="uploadModule">
            <template v-if="state.uploadState == 'wait-file'">
                <Icon size="50px" color="#909399" name="el-icon-UploadFilled" />
                <div class="el-upload__text">
                    {{ $t('module.Drag the module package file here') }} <em>{{ $t('module.Click me to upload') }}</em>
                </div>
            </template>
            <el-result v-else icon="success" :sub-title="$t('module.Uploaded, installation is about to start, please wait')"></el-result>
        </el-upload>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { UploadFile } from 'element-plus'
import { fileUpload } from '/@/api/common'
import { upload } from '/@/api/backend/module'
import { onInstall } from '../index'

const state = reactive({
    uploadState: 'wait-file',
})

const uploadModule = (file: UploadFile) => {
    if (!file || !file.raw) return
    let fd = new FormData()
    fd.append('file', file.raw!)
    fileUpload(fd).then((res) => {
        if (res.code == 1) {
            upload(res.data.file.url).then((res) => {
                onInstall(res.data.info.uid, 0)
            })
            state.uploadState = 'success'
        }
    })
}
</script>

<style scoped lang="scss">
.tips {
    padding: 20px;
    background-color: var(--el-bg-color-page);
    border-radius: var(--el-border-radius-base);
    max-width: 400px;
    margin: 0 auto;
    color: var(--el-color-danger);
    .title {
        font-size: var(--el-font-size-medium);
        padding-bottom: 6px;
    }
    .tip-item {
        font-size: var(--el-font-size-base);
    }
}
.upload-module {
    max-width: 460px;
    margin: 40px auto;
}
</style>
