<template>
    <div>
        <div class="install-conflict">
            <template v-if="state.common.fileConflict.length > 0">
                <div class="install-title">文件冲突</div>
                <el-table :data="state.common.fileConflict" stripe border style="width: 100%">
                    <el-table-column prop="newFile" label="新文件" />
                    <el-table-column prop="oldFile" label="已有文件" />
                    <el-table-column prop="solution" width="200" label="处理方案" align="center">
                        <template #default="scope">
                            <el-select v-model="scope.row.solution">
                                <el-option label="备份并覆盖已有文件" value="cover"></el-option>
                                <el-option label="丢弃新文件" value="discard"></el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
            <template v-if="state.common.dependConflict.length > 0">
                <div class="install-title">依赖冲突</div>
                <el-table :data="state.common.dependConflict" stripe border style="width: 100%">
                    <el-table-column prop="env" label="环境">
                        <template #default="scope">
                            <span v-if="scope.row.env">{{ $t('module.env ' + scope.row.env) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="newDepend" label="新依赖" />
                    <el-table-column prop="oldDepend" label="已有依赖" />
                    <el-table-column prop="solution" width="200" label="处理方案" align="center">
                        <template #default="scope">
                            <el-select v-model="scope.row.solution">
                                <el-option label="覆盖已有依赖" value="cover"></el-option>
                                <el-option label="不使用新依赖" value="discard"></el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </div>
        <el-button
            v-blur
            class="install-done-button"
            :loading="state.loading.common"
            :disabled="state.loading.common"
            size="large"
            type="primary"
            @click="onSubmitConflictHandle"
            >{{ $t('Confirm') }}</el-button
        >
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { execInstall } from '../index'

const onSubmitConflictHandle = () => {
    state.loading.common = true
    let fileConflict: anyObj = {},
        dependConflict: anyObj = {}
    for (const key in state.common.fileConflict) {
        fileConflict[state.common.fileConflict[key].oldFile] = state.common.fileConflict[key]['solution']
    }
    for (const key in state.common.dependConflict) {
        if (typeof dependConflict[state.common.dependConflict[key].env] == 'undefined') {
            dependConflict[state.common.dependConflict[key].env] = {}
        }
        dependConflict[state.common.dependConflict[key].env][state.common.dependConflict[key].depend] = state.common.dependConflict[key]['solution']
    }
    execInstall(state.common.uid, 0, {
        dependConflict: dependConflict,
        fileConflict: fileConflict,
    })
}
</script>

<style scoped lang="scss">
.install-conflict {
    min-height: 400px;
}
.install-title {
    font-size: var(--el-font-size-large);
    text-align: center;
    padding: 20px;
}
.install-done-button {
    display: block;
    margin: 20px auto;
    width: 120px;
}
</style>
