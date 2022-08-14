<template>
    <div>
        <el-dialog v-model="state.install.showDialog" custom-class="install-dialog" title="安装" width="60%">
            <el-scrollbar :height="500">
                <div v-loading="state.install.loading" :element-loading-text="$t('module.stateTitle ' + state.install.stateTitle)" :key="state.install.componentKey" class="install-loading"></div>
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state } from '../index'
</script>

<style scoped lang="scss">
:deep(.install-dialog) .el-dialog__body {
    padding: 10px 20px;
}
.install-loading {
    height: 500px;
}
</style>
