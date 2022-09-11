<template>
    <div class="upload-install">
        <div class="tips">
            <div class="title">请您务必确认模块包文件来自官方渠道或经由官方认证的模块作者，否则系统可能被破坏，因为：</div>
            <div class="tip-item">1. 模块可以修改和新增系统文件</div>
            <div class="tip-item">2. 模块可以执行sql命令和代码</div>
            <div class="tip-item">3. 模块可以安装新的前后端依赖</div>
        </div>
        <el-upload class="upload-module" :show-file-list="false" accept=".zip" drag :auto-upload="false" @change="uploadModule">
            <template v-if="state.uploadState == 'wait-file'">
                <Icon size="50px" color="#909399" name="el-icon-UploadFilled" />
                <div class="el-upload__text">拖拽模块包文件到此处或 <em>点击我上传</em></div>
            </template>
            <el-result v-else icon="success" sub-title="已上传，即将开始安装，请稍等..."></el-result>
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
