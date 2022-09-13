<template>
    <div>
        <el-dialog
            :close-on-press-escape="state.common.quickClose"
            :title="state.common.dialogTitle"
            :close-on-click-modal="state.common.quickClose"
            v-model="state.dialog.common"
            custom-class="common-dialog"
        >
            <el-scrollbar :height="500">
                <!-- 公共dialog形式的loading -->
                <div
                    v-if="state.common.type == 'loading' || state.common.type == 'waitFullReload'"
                    v-loading="true"
                    :element-loading-text="state.common.loadingTitle ? $t('module.stateTitle ' + state.common.loadingTitle) : ''"
                    :key="state.common.loadingComponentKey"
                    class="common-loading"
                    :style="{ height: state.common.type == 'loading' ? '400px' : '250px' }"
                ></div>

                <!-- 安装冲突 -->
                <InstallConflict v-if="state.common.type == 'InstallConflict'" />

                <!-- 禁用冲突 -->
                <ConfirmFileConflict v-if="state.common.type == 'disableConfirmConflict'" />

                <!-- 安装/禁用结束 -->
                <CommonDone v-if="state.common.type == 'done'" />

                <!-- 上传安装 -->
                <UploadInstall v-if="state.common.type == 'uploadInstall'" />

                <!-- 等待热更新 -->
                <div v-if="state.common.type == 'waitFullReload'" class="full-reload-tips">
                    若您未在
                    <el-link target="_blank" type="primary" href="https://wonderful-code.gitee.io/guide/other/developerMustSee.html#开发环境">
                        开发环境
                    </el-link>
                    下或页面未自动刷新，请<el-link type="primary" @click="triggerFullReload">点击我继续</el-link>
                </div>
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { triggerFullReload } from '../index'
import InstallConflict from './installConflict.vue'
import CommonDone from './commonDone.vue'
import UploadInstall from './uploadInstall.vue'
import ConfirmFileConflict from './confirmFileConflict.vue'
</script>

<style scoped lang="scss">
:deep(.common-dialog) .el-dialog__body {
    padding: 10px 20px;
}
.common-dialog {
    height: 500px;
}
.full-reload-tips {
    display: flex;
    justify-content: center;
    color: var(--el-color-warning);
}
@media screen and (max-width: 1440px) {
    :deep(.common-dialog) {
        --el-dialog-width: 60% !important;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.common-dialog) {
        --el-dialog-width: 80% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.common-dialog) {
        --el-dialog-width: 92% !important;
    }
}
</style>
