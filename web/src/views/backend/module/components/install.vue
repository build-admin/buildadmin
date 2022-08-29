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
                    v-if="
                        !state.waitFullReload &&
                        (state.install.state == moduleInstallState.INSTALLED || state.install.state == moduleInstallState.DEPENDENT_WAIT_INSTALL)
                    "
                />
                <div v-if="state.waitFullReload" class="install-wait-full-reload">
                    <div v-loading="true" element-loading-text="WEB文件已更新，等待热重载..." class="wait-full-reload-loading"></div>
                    <div class="full-reload-tips">
                        若您未在
                        <el-link target="_blank" type="primary" href="https://wonderful-code.gitee.io/guide/other/developerMustSee.html#开发环境">
                            开发环境
                        </el-link>
                        下，请<el-link type="primary" @click="nonDevMode">点击我</el-link>继续完成安装
                    </div>
                </div>
            </el-scrollbar>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { state, onInstall } from '../index'
import { moduleInstallState } from '../types'
import { INSTALL_MODULE_TEMP } from '/@/stores/constant/cacheKey'
import InstallConflict from '/@/views/backend/module/components/installConflict.vue'
import InstallDone from '/@/views/backend/module/components/installDone.vue'
import { Session } from '/@/utils/storage'
import { VITE_FULL_RELOAD } from '/@/stores/constant/cacheKey'
import { useRouter } from 'vue-router'

const router = useRouter()
const installModuleTemp = Session.get(INSTALL_MODULE_TEMP)
if (installModuleTemp) {
    onInstall(installModuleTemp.uid, installModuleTemp.id)
}

const nonDevMode = () => {
    Session.set(VITE_FULL_RELOAD, true)
    router.go(0)
}
</script>

<style scoped lang="scss">
:deep(.install-dialog) .el-dialog__body {
    padding: 10px 20px;
}
.install-loading {
    height: 500px;
}
.install-wait-full-reload {
    height: 500px;
    .wait-full-reload-loading {
        height: 250px;
    }
    .full-reload-tips {
        display: flex;
        justify-content: center;
        color: var(--el-color-warning);
    }
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
