<template>
    <div>
        <el-dialog
            :close-on-press-escape="false"
            :close-on-click-modal="false"
            v-model="state.install.showDialog"
            custom-class="install-dialog"
            :title="state.install.title"
            width="60%"
        >
            <el-scrollbar :height="500">
                <div
                    v-loading="state.install.loading"
                    v-if="state.install.loading"
                    :element-loading-text="$t('module.stateTitle ' + state.install.stateTitle)"
                    :key="state.install.componentKey"
                    class="install-loading"
                ></div>
                <InstallConflict v-if="state.install.state == moduleInstallState.CONFLICT_PENDING" />
                <InstallDone
                    v-if="state.install.state == moduleInstallState.INSTALLED || state.install.state == moduleInstallState.DEPENDENT_WAIT_INSTALL"
                />
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state, moduleInstallState, onInstall } from '../index'
import { INSTALL_MODULE_TEMP } from '/@/stores/constant/cacheKey'
import InstallConflict from '/@/views/backend/module/components/installConflict.vue'
import InstallDone from '/@/views/backend/module/components/installDone.vue'
import { Session } from '/@/utils/storage'

const installModuleTemp = Session.get(INSTALL_MODULE_TEMP)
if (installModuleTemp) {
    onInstall(installModuleTemp.uid, installModuleTemp.id, 'sessionStorage')
}
</script>

<style scoped lang="scss">
:deep(.install-dialog) .el-dialog__body {
    padding: 10px 20px;
}
.install-loading {
    height: 500px;
}
:deep(.install-done-button) {
    display: block;
    margin: 20px auto;
    width: 120px;
}
@media screen and (max-width: 1440px) {
    :deep(.install-dialog) {
        --el-dialog-width: 60% !important;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.install-dialog) {
        --el-dialog-width: 80% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.install-dialog) {
        --el-dialog-width: 92% !important;
    }
}
</style>
