<template>
    <div>
        <div class="confirm-file-conflict">
            <template v-if="state.common.disableConflictFile.length">
                <div class="conflict-title">文件冲突</div>
                <el-alert
                    :closable="false"
                    :center="true"
                    title="检测到以下的模块文件有更新，禁用时将自动覆盖，请注意备份。"
                    class="alert-warning"
                    type="warning"
                ></el-alert>
                <el-table :data="state.common.disableConflictFile" stripe border :style="{ width: '100%', marginBottom: '20px' }">
                    <el-table-column prop="file" label="冲突文件" />
                </el-table>
            </template>
            <template v-if="state.common.disableDependConflict">
                <div class="conflict-title">依赖冲突</div>
                <div class="depend-conflict-tips">禁用后，系统依赖项将被还原到模块<span class="text-bold">安装之前</span>，请注意备份！</div>
                <el-alert
                    :closable="false"
                    :center="true"
                    title="composer.json 和 web/package.json 文件将被还原"
                    class="alert-warning"
                    type="error"
                ></el-alert>
            </template>
        </div>
        <div class="center-buttons">
            <el-button
                v-blur
                class="center-button"
                :loading="state.loading.common"
                :disabled="state.loading.common"
                size="large"
                type="primary"
                @click="onDisable(true)"
            >
                确认禁用模块
            </el-button>
            <el-button v-blur class="center-button" size="large" @click="cancelDisable()"> 取消 </el-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { onDisable } from '../index'

const cancelDisable = () => {
    state.dialog.common = false
    state.goodsInfo.enable = true
}
</script>

<style scoped lang="scss">
.confirm-file-conflict {
    min-height: 400px;
}
.conflict-alert {
    width: 500px;
    margin: 0 auto;
}
.alert-warning {
    margin: 20px auto;
    width: 500px;
}
.depend-conflict-tips {
    text-align: center;
}
.text-bold {
    font-weight: bold;
}
.conflict-title {
    font-size: var(--el-font-size-large);
    text-align: center;
    margin-bottom: 20px;
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
