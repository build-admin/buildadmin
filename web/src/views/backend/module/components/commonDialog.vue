<template>
    <div>
        <el-dialog
            :close-on-press-escape="state.common.quickClose"
            :title="state.common.dialogTitle"
            :close-on-click-modal="state.common.quickClose"
            v-model="state.dialog.common"
            class="common-dialog"
        >
            <el-scrollbar :height="500">
                <!-- 公共dialog形式的loading -->
                <div
                    v-if="state.common.type == 'loading'"
                    v-loading="true"
                    :element-loading-text="state.common.loadingTitle ? $t('module.stateTitle ' + state.common.loadingTitle) : ''"
                    :key="state.common.loadingComponentKey"
                    class="common-loading"
                ></div>

                <!-- 安装冲突 -->
                <InstallConflict v-if="state.common.type == 'InstallConflict'" />

                <!-- 禁用冲突 -->
                <ConfirmFileConflict v-if="state.common.type == 'disableConfirmConflict'" />

                <!-- 安装/禁用结束 -->
                <CommonDone v-if="state.common.type == 'done'" />

                <!-- 上传安装 -->
                <UploadInstall v-if="state.common.type == 'uploadInstall'" />
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
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
.common-loading {
    height: 400px;
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
