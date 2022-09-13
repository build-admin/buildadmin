<template>
    <div>
        <div class="confirm-file-conflict">
            <template v-if="state.common.disableConflictFile.length">
                <div class="conflict-title">{{ $t('module.File conflict') }}</div>
                <el-alert :closable="false" :center="true" :title="$t('module.Update warning')" class="alert-warning" type="warning"></el-alert>
                <el-table :data="state.common.disableConflictFile" stripe border :style="{ width: '100%', marginBottom: '20px' }">
                    <el-table-column prop="file" :label="$t('module.Conflict file')" />
                </el-table>
            </template>
            <template v-if="state.common.disableDependConflict">
                <div class="conflict-title">{{ $t('module.Dependency conflict') }}</div>
                <div class="depend-conflict-tips">
                    {{ $t('module.Dependency recovery warning 1') }}
                    <span class="text-bold">{{ $t('module.Dependency recovery warning 2') }}</span>
                    {{ $t('module.Dependency recovery warning 3') }}
                </div>
                <el-alert
                    :closable="false"
                    :center="true"
                    :title="$t('module.composer and package The JSON file will be restored')"
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
                {{ $t('module.Confirm to disable the module') }}
            </el-button>
            <el-button v-blur class="center-button" size="large" @click="cancelDisable()"> {{ $t('Cancel') }} </el-button>
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
