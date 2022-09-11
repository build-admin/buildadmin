<template>
    <div class="upload-install">
        <div class="tips">
            <div class="title">请您务必确认模块包文件来自官方渠道或经由官方认证的模块作者，否则系统可能被破坏，因为：</div>
            <div class="tip-item">1. 模块可以修改和新增系统文件</div>
            <div class="tip-item">2. 模块可以执行sql命令和代码</div>
            <div class="tip-item">3. 模块可以安装新的前后端依赖</div>
        </div>
        <el-upload class="upload-module" accept=".zip" drag action="https://run.mocky.io/v3/9d059bf9-4660-45f2-925d-ce80ad6c4d15">
            <Icon size="50px" color="#909399" name="el-icon-UploadFilled" />
            <div class="el-upload__text">拖拽模块包文件到此处或 <em>点击我上传</em></div>
        </el-upload>
    </div>
</template>

<script setup lang="ts"></script>

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
