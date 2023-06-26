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

            <template v-if="state.common.disableDependConflict.length > 0">
                <div class="conflict-title">{{ $t('module.The module declares the added dependencies') }}</div>
                <el-table :data="state.common.disableDependConflict" stripe border style="width: 100%">
                    <el-table-column prop="env" :label="$t('module.environment')">
                        <template #default="scope">
                            <span v-if="scope.row.env">{{ $t('module.env ' + scope.row.env) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="dependTitle" :label="$t('module.Dependencies')" />
                    <el-table-column prop="solution" width="200" :label="$t('module.Treatment scheme')" align="center">
                        <template #default="scope">
                            <el-select v-model="scope.row.solution">
                                <el-option :label="$t('Delete')" value="delete"></el-option>
                                <el-option :label="$t('module.retain')" value="retain"></el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                </el-table>
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
