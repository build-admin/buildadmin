<template>
    <div>
        <el-dialog v-model="state.goodsInfo.showConfirmConflict" top="20vh" custom-class="confirm-file-conflict" title="确定冲突" width="55%">
            <template v-if="state.goodsInfo.conflictFile.length">
                <div class="conflict-title">文件冲突</div>
                <el-alert
                    :closable="false"
                    :center="true"
                    title="检测到以下的模块文件有更新，禁用时将自动覆盖，请注意备份。"
                    class="alert-warning"
                    type="warning"
                ></el-alert>
                <el-table :data="state.goodsInfo.conflictFile" stripe border :style="{ width: '100%', marginBottom: '20px' }">
                    <el-table-column prop="file" label="冲突文件" />
                </el-table>
            </template>
            <template v-if="state.goodsInfo.dependConflict">
                <div class="conflict-title mb-20">依赖冲突</div>
                <el-alert
                    :closable="false"
                    :center="true"
                    title="禁用后，系统依赖项将被还原到模块安装之前，包含 composer.json 文件和 web/package.json 文件，请注意备份。"
                    class="alert-warning"
                    type="warning"
                ></el-alert>
            </template>
            <div class="center-buttons">
                <el-button
                    v-blur
                    class="center-button"
                    :loading="state.publicButtonLoading"
                    :disabled="state.publicButtonLoading"
                    size="large"
                    type="primary"
                    @click="postDisable(true)"
                >
                    确认禁用模块
                </el-button>
                <el-button v-blur class="center-button" size="large" @click="state.goodsInfo.showConfirmConflict = false"> 取消 </el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state, postDisable } from '../index'
</script>

<style scoped lang="scss">
:deep(.confirm-file-conflict) .el-dialog__body {
    padding: 10px 20px;
}
.conflict-title {
    font-size: var(--el-font-size-large);
    text-align: center;
}
.mb-20 {
    margin-bottom: 20px;
}
.alert-warning {
    margin: 10px 0;
}
.center-buttons {
    display: flex;
    justify-content: center;
    margin: 20px auto;
}
.center-button {
    width: 120px;
}
</style>
