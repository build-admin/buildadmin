<template>
    <div class="install-done">
        <div class="install-done-title">
            <span v-if="state.common.moduleState == moduleInstallState.INSTALLED">
                {{ t('module.Congratulations, module installation is complete') }}
            </span>
            <span v-else-if="state.common.moduleState == moduleInstallState.DISABLE">{{ t('module.Module is disabled') }}</span>
            <span v-else-if="state.common.moduleState == moduleInstallState.DEPENDENT_WAIT_INSTALL">
                {{ t('module.Congratulations, the code of the module is ready') }}
            </span>
            <span v-else>{{ t('module.Unknown state') }}</span>
        </div>
        <div class="install-tis-box">
            <div v-if="state.common.dependInstallState != 'none'" class="depend-box">
                <div class="depend-loading" v-if="state.common.dependInstallState == 'executing'" v-loading="true"></div>
                <div class="depend-tis">
                    <div v-if="state.common.dependInstallState == 'executing'">
                        <span class="color-red">{{ t('module.Do not refresh the page!') }}</span>
                        <span v-if="state.common.moduleState == moduleInstallState.DISABLE">
                            {{ t('module.New adjustment of dependency detected') }}
                        </span>
                        <span v-else-if="state.common.moduleState == moduleInstallState.DEPENDENT_WAIT_INSTALL">
                            {{ t('module.This module adds new dependencies') }} </span
                        >，
                        <span>
                            {{ t('module.The built-in terminal of the system is automatically installing these dependencies, please wait~') }}
                        </span>
                        <span class="span-a" @click="showTerminal">{{ t('module.View progress') }}</span>
                    </div>
                    <div v-if="state.common.dependInstallState == 'success'" class="color-green">
                        {{ t('module.Dependency installation completed~') }}
                    </div>
                    <div v-if="state.common.dependInstallState == 'fail'" class="exec-fail color-red">
                        {{ t('module.Dependency installation fail 1') }}
                        <span class="span-a" @click="showTerminal">{{ t('module.Dependency installation fail 2') }}</span>
                        {{ t('module.Dependency installation fail 3') }}
                        <el-link target="_blank" type="primary" href="https://doc.buildadmin.com/guide/install/manualOperation.html">
                            {{ t('module.Dependency installation fail 4') }}
                        </el-link>
                    </div>
                </div>
            </div>
            <div v-else-if="state.common.moduleState == moduleInstallState.INSTALLED" class="depend-tis">
                {{ t('module.This module does not add new dependencies') }}
            </div>
            <div v-else>{{ t('module.There is no adjustment for system dependency') }}</div>
        </div>
        <div v-if="state.common.dependInstallState == 'fail'" class="install-tis-box text-align-center">
            <div class="install-tis">
                {{ t('module.Dependency installation fail 5') }}
                <span class="span-a" @click="onConfirmDepend">
                    {{ t('module.Dependency installation fail 6') }}
                </span>
                {{ t('module.Dependency installation fail 7') }}
                <span class="dependency-installation-fail-tips">
                    {{ t('module.dependency-installation-fail-tips') }}
                </span>
            </div>
        </div>
        <div class="install-tis-box">
            <div class="install-tis">
                {{ t('module.please') }}{{ state.common.moduleState == moduleInstallState.DISABLE ? '' : t('module.After installation 1')
                }}{{ t('module.Manually clean up the system and browser cache') }}
            </div>
        </div>
        <div class="install-tis-box">
            <div class="install-form">
                <FormItem
                    :label="
                        (state.common.moduleState == moduleInstallState.DISABLE ? '' : t('module.After installation 2')) +
                        t('module.Automatically execute reissue command?')
                    "
                    v-model="form.rebuild"
                    type="radio"
                    :input-attr="{
                        border: true,
                        content: { 0: t('module.no'), 1: t('module.yes') },
                    }"
                />
            </div>
        </div>
        <el-button
            v-blur
            class="install-done-button"
            :disabled="state.common.dependInstallState != 'executing' || state.common.moduleState == moduleInstallState.INSTALLED ? false : true"
            size="large"
            type="primary"
            v-loading="state.loading.common"
            @click="onSubmitInstallDone"
        >
            {{ state.common.moduleState == moduleInstallState.DISABLE ? t('Complete') : t('module.End of installation') }}
        </el-button>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { state } from '../store'
import { moduleInstallState } from '../types'
import { onRefreshTableData } from '../index'
import { useTerminal } from '/@/stores/terminal'
import FormItem from '/@/components/formItem/index.vue'
import { taskStatus } from '/@/stores/constant/terminalTaskStatus'
import { ElMessageBox } from 'element-plus'
import { useI18n } from 'vue-i18n'
import { dependentInstallComplete } from '/@/api/backend/module'
import { reloadServer } from '/@/utils/vite'

const { t } = useI18n()
const terminal = useTerminal()
const form = reactive({
    rebuild: 0,
})

const showTerminal = () => {
    terminal.toggle(true)
}

const onSubmitInstallDone = () => {
    state.dialog.common = false
    if (form.rebuild == 1) {
        terminal.toggle(true)
        terminal.addTaskPM('web-build', false, '', (res: number) => {
            if (res == taskStatus.Success) {
                terminal.toggle(false)
                if (state.common.moduleState != moduleInstallState.DISABLE) {
                    reloadServer('modules')
                }
            }
        })
    } else if (state.common.moduleState != moduleInstallState.DISABLE) {
        reloadServer('modules')
    }
}

const onConfirmDepend = () => {
    ElMessageBox.confirm(t('module.Is the command that failed on the WEB terminal executed manually or in other ways successfully?'), t('Reminder'), {
        confirmButtonText: t('module.yes'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    }).then(() => {
        state.loading.common = true
        dependentInstallComplete(state.common.uid).then(() => {
            onRefreshTableData()
            state.loading.common = false
            state.common.dependInstallState = 'success'
        })
    })
}
</script>

<style scoped lang="scss">
.install-done-title {
    font-size: var(--el-font-size-extra-large);
    color: var(--el-color-success);
    text-align: center;
}
.text-align-center {
    text-align: center;
}
.install-tis-box {
    padding: 20px;
    margin: 20px auto;
    width: 70%;
    border: 1px solid var(--el-border-color-lighter);
    border-radius: var(--el-border-radius-base);
    display: flex;
    align-items: center;
    justify-content: center;
    .dependency-installation-fail-tips {
        display: block;
        font-size: var(--el-font-size-extra-small);
        text-align: center;
        padding-top: 5px;
        color: var(--el-text-color-regular);
    }
}
.depend-box {
    display: flex;
    align-items: center;
    justify-content: center;
}
.install-tis {
    color: var(--el-color-warning);
}
.depend-loading {
    width: 30px;
    height: 30px;
    margin-right: 36px;
}
.span-a {
    color: var(--el-color-primary);
    cursor: pointer;
    &:hover {
        color: var(--el-color-primary-light-5);
    }
}
.install-form :deep(.ba-input-item-radio) {
    margin-bottom: 0;
}
.exec-fail {
    display: flex;
}
.color-red {
    color: var(--el-color-danger);
}
.color-green {
    color: var(--el-color-success);
}
.install-done-button {
    display: block;
    margin: 20px auto;
    width: 120px;
}
@media screen and (max-width: 1600px) {
    :deep(.install-tis-box) {
        width: 76%;
    }
}
@media screen and (max-width: 1280px) {
    :deep(.install-tis-box) {
        width: 80%;
    }
}
@media screen and (max-width: 900px) {
    :deep(.install-tis-box) {
        width: 96%;
        flex-wrap: wrap;
    }
}
</style>
