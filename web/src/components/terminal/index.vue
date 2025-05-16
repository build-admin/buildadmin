<template>
    <div>
        <el-dialog v-model="terminal.state.show" :title="t('terminal.Terminal')" class="ba-terminal-dialog main-dialog">
            <el-scrollbar ref="terminalScrollbarRef" :max-height="500" class="terminal-scrollbar">
                <el-alert
                    class="terminal-warning-alert"
                    v-if="!terminal.state.phpDevelopmentServer"
                    :title="t('terminal.The current terminal is not running under the installation service, and some commands may not be executed')"
                    type="error"
                />

                <el-timeline class="terminal-timeline" v-if="terminal.state.taskList.length">
                    <el-timeline-item
                        v-for="(item, idx) in terminal.state.taskList"
                        :key="idx"
                        class="task-item"
                        :class="'task-status-' + item.status"
                        :type="getTaskStatus(item.status)['statusType']"
                        center
                        :timestamp="item.createTime"
                        placement="top"
                    >
                        <el-card>
                            <div>
                                <el-tag :type="getTaskStatus(item.status)['statusType']">{{ getTaskStatus(item.status)['statusText'] }}</el-tag>
                                <el-tag
                                    class="block-on-failure-tag"
                                    v-if="(item.status == taskStatus.Failed || item.status == taskStatus.Unknown) && item.blockOnFailure"
                                    type="warning"
                                >
                                    {{ t('terminal.Failure to execute this command will block the execution of the queue') }}
                                </el-tag>
                                <el-tag
                                    class="block-on-failure-tag"
                                    v-if="item.status == taskStatus.Executing || item.status == taskStatus.Connecting"
                                    type="danger"
                                >
                                    {{ t('terminal.Do not refresh the browser') }}
                                </el-tag>
                                <span class="command">{{ item.command }}</span>
                                <div class="task-opt">
                                    <el-button
                                        :title="t('Retry')"
                                        v-if="item.status == taskStatus.Failed || item.status == taskStatus.Unknown"
                                        size="small"
                                        v-blur
                                        type="warning"
                                        icon="el-icon-RefreshRight"
                                        circle
                                        @click="terminal.retryTask(idx)"
                                    />
                                    <el-button
                                        @click="terminal.delTask(idx)"
                                        :title="t('Delete')"
                                        size="small"
                                        v-blur
                                        type="danger"
                                        icon="el-icon-Delete"
                                        circle
                                    />
                                </div>
                            </div>
                            <template v-if="item.status != taskStatus.Waiting">
                                <div
                                    v-if="item.status != taskStatus.Connecting && item.status != taskStatus.Executing"
                                    @click="terminal.setTaskShowMessage(idx)"
                                    class="toggle-message-display"
                                >
                                    <span>{{ t('terminal.Command run log') }}</span>
                                    <Icon :name="item.showMessage ? 'el-icon-ArrowUp' : 'el-icon-ArrowDown'" size="16" color="#909399" />
                                </div>
                                <div
                                    v-if="
                                        item.status == taskStatus.Connecting ||
                                        item.status == taskStatus.Executing ||
                                        (item.status > taskStatus.Executing && item.showMessage)
                                    "
                                    class="exec-message"
                                    :class="'exec-message-' + item.uuid"
                                >
                                    <pre v-for="(msg, index) in item.message" :key="index" class="message-item">{{ msg }}</pre>
                                </div>
                            </template>
                        </el-card>
                    </el-timeline-item>
                </el-timeline>
                <el-empty v-else :image-size="80" :description="t('terminal.No mission yet')" />
            </el-scrollbar>

            <div class="terminal-buttons">
                <el-button class="terminal-menu-item" icon="el-icon-MagicStick" v-blur @click="addTerminalTask('test', true, false)">
                    {{ t('terminal.Test command') }}
                </el-button>
                <el-dropdown class="terminal-menu-item">
                    <el-button icon="el-icon-Download" v-blur>
                        {{ t('terminal.Install dependent packages') }}
                    </el-button>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item @click="addTerminalTask('web-install', true)" v-if="terminal.state.packageManager != 'none'">
                                {{ terminal.state.packageManager }} run install
                            </el-dropdown-item>
                            <el-dropdown-item @click="addTerminalTask('composer.update', false)">composer update</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
                <el-button class="terminal-menu-item" icon="el-icon-Sell" v-blur @click="webBuild()">{{ t('terminal.Republish') }}</el-button>
                <el-button class="terminal-menu-item" icon="el-icon-Delete" v-blur @click="terminal.clearSuccessTask()">
                    {{ t('terminal.Clean up task list') }}
                </el-button>
                <el-button class="terminal-menu-item" icon="el-icon-Tools" v-blur @click="terminal.toggleConfigDialog()">
                    {{ t('terminal.Terminal settings') }}
                </el-button>
            </div>
        </el-dialog>

        <el-dialog
            @close="terminal.toggleConfigDialog(false)"
            :model-value="terminal.state.showConfig"
            class="ba-terminal-dialog"
            :title="t('terminal.Terminal settings')"
        >
            <el-form label-position="left" label-width="140">
                <FormItem
                    :label="'NPM ' + t('terminal.Source')"
                    :model-value="terminal.state.npmRegistry"
                    :key="terminal.state.npmRegistry"
                    v-loading="state.registryLoading && state.registryLoadingType == 'npm'"
                    type="select"
                    :input-attr="{
                        border: true,
                        content: getSourceContent('npm'),
                        teleported: false,
                        onChange: (val: string) => changeRegistry(val, 'npm'),
                    }"
                />
                <FormItem
                    :label="'Composer ' + t('terminal.Source')"
                    :model-value="terminal.state.composerRegistry"
                    :key="terminal.state.composerRegistry"
                    v-loading="state.registryLoading && state.registryLoadingType == 'composer'"
                    type="select"
                    :input-attr="{
                        border: true,
                        content: getSourceContent('composer'),
                        teleported: false,
                        onChange: (val: string) => changeRegistry(val, 'composer'),
                    }"
                />
                <FormItem
                    :label="t('terminal.NPM package manager')"
                    :model-value="terminal.state.packageManager"
                    v-loading="state.packageManagerLoading"
                    type="select"
                    :input-attr="{
                        border: true,
                        content: { npm: 'NPM', cnpm: 'CNPM', pnpm: 'PNPM', yarn: 'YARN', ni: 'NI', none: t('terminal.Manual execution') },
                        teleported: false,
                        onChange: (val: string) => changePackageManager(val),
                    }"
                    :tip="t('terminal.NPM package manager tip')"
                />
                <FormItem
                    :label="t('terminal.Clear successful task')"
                    :model-value="terminal.state.automaticCleanupTask"
                    type="radio"
                    :input-attr="{
                        border: true,
                        content: { '0': t('Disable'), '1': t('Enable') },
                        onChange: terminal.changeAutomaticCleanupTask,
                    }"
                    :tip="t('terminal.Clear successful task tip')"
                />
            </el-form>
            <div class="config-buttons">
                <el-button @click="terminal.toggleConfigDialog(false)">{{ t('terminal.Back to terminal') }}</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import type { TimelineItemProps } from 'element-plus'
import { ElMessageBox, ElScrollbar } from 'element-plus'
import { nextTick, onMounted, reactive, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { postChangeTerminalConfig } from '/@/api/common'
import FormItem from '/@/components/formItem/index.vue'
import { taskStatus } from '/@/stores/constant/terminalTaskStatus'
import { useTerminal } from '/@/stores/terminal'
import { changeListenDirtyFileSwitch } from '/@/utils/vite'

type SourceType = 'npm' | 'composer'

const { t } = useI18n()
const terminal = useTerminal()
const terminalScrollbarRef = useTemplateRef('terminalScrollbarRef')

const state = reactive({
    registryLoading: false,
    registryLoadingType: 'npm',
    packageManagerLoading: false,
})

const getTaskStatus = (status: number) => {
    let statusText = t('terminal.unknown')
    let statusType: TimelineItemProps['type'] = 'info'
    switch (status) {
        case taskStatus.Waiting:
            statusText = t('terminal.Waiting for execution')
            statusType = 'info'
            break
        case taskStatus.Connecting:
            statusText = t('terminal.Connecting')
            statusType = 'warning'
            break
        case taskStatus.Executing:
            statusText = t('terminal.Executing')
            statusType = 'warning'
            break
        case taskStatus.Success:
            statusText = t('terminal.Successful execution')
            statusType = 'success'
            break
        case taskStatus.Failed:
            statusText = t('terminal.Execution failed')
            statusType = 'danger'
            break
        case taskStatus.Unknown:
            statusText = t('terminal.Unknown execution result')
            statusType = 'danger'
            break
    }
    return {
        statusText: statusText,
        statusType: statusType,
    }
}

const addTerminalTask = (command: string, pm: boolean, blockOnFailure = true, extend = '', callback: Function = () => {}) => {
    if (pm) {
        terminal.addTaskPM(command, blockOnFailure, extend, callback)
    } else {
        terminal.addTask(command, blockOnFailure, extend, callback)
    }

    // 任务列表滚动条滚动到底部
    nextTick(() => {
        if (terminalScrollbarRef.value && terminalScrollbarRef.value.wrapRef) {
            terminalScrollbarRef.value.setScrollTop(terminalScrollbarRef.value.wrapRef.scrollHeight)
        }
    })
}

const webBuild = () => {
    ElMessageBox.confirm(t('terminal.Are you sure you want to republish?'), t('Reminder'), {
        confirmButtonText: t('Confirm'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    }).then(() => {
        changeListenDirtyFileSwitch(false)
        addTerminalTask('web-build', true, true, '', () => {
            changeListenDirtyFileSwitch(true)
        })
    })
}

const changePackageManager = (val: string) => {
    state.packageManagerLoading = true
    postChangeTerminalConfig({ manager: val })
        .then((res) => {
            if (res.code == 1) {
                terminal.changePackageManager(val)
            }
        })
        .finally(() => {
            state.packageManagerLoading = false
        })
}

const changeRegistry = (val: string, type: SourceType) => {
    const oldVal = type == 'npm' ? terminal.state.npmRegistry : terminal.state.composerRegistry
    terminal.changeRegistry(val, type)
    state.registryLoading = true
    state.registryLoadingType = type
    terminal.addTask(`set-${type}-registry.${val}`, false, '', (res: taskStatus) => {
        state.registryLoading = false
        if (res == taskStatus.Failed || res == taskStatus.Unknown) {
            ElMessageBox.confirm(t('terminal.Failed to modify the source command, Please try again manually'), t('Reminder'), {
                confirmButtonText: t('Confirm'),
                showCancelButton: false,
                type: 'warning',
            }).then(() => {
                terminal.changeRegistry(oldVal, type)
            })
        }
    })
}

const getSourceContent = (type: SourceType) => {
    let content: anyObj = {}
    if (type == 'npm') {
        content = { npm: 'npm', taobao: 'taobao', tencent: 'tencent' }
    } else if (type == 'composer') {
        content = {
            composer: 'composer',
            huawei: 'huawei',
            aliyun: 'aliyun',
            tencent: 'tencent',
            kkame: 'kkame',
        }
    }

    // 如果值为 unknown，则 unknown 选项
    if (terminal.state[type == 'npm' ? 'npmRegistry' : 'composerRegistry'] == 'unknown') {
        content.unknown = t('Unknown')
    }
    return content
}

onMounted(() => {
    terminal.init()
})
</script>

<style scoped lang="scss">
.terminal-warning-alert {
    margin: 0 0 20px 0;
}
.terminal-timeline {
    padding: 0 15px;
}
.command {
    font-size: var(--el-font-size-large);
    font-weight: bold;
    margin-left: 10px;
}
.exec-message {
    color: var(--ba-bg-color-overlay);
    font-size: 12px;
    line-height: 16px;
    padding: 6px;
    background-color: #424251;
    margin-top: 10px;
    min-height: 30px;
    max-height: 200px;
    overflow: auto;
    &::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    &::-webkit-scrollbar-thumb {
        background: #c8c9cc;
        border-radius: 4px;
        box-shadow: none;
        -webkit-box-shadow: none;
    }
    &::-webkit-scrollbar-track {
        background: var(--ba-bg-color);
    }
    &:hover {
        &::-webkit-scrollbar-thumb:hover {
            background: #909399;
        }
    }
}
@supports not (selector(::-webkit-scrollbar)) {
    .exec-message {
        scrollbar-width: thin;
        scrollbar-color: #c8c9cc #eaeaea;
    }
}
.toggle-message-display {
    padding-top: 10px;
    font-size: 13px;
    color: var(--el-text-color-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.task-opt {
    display: none;
    float: right;
}
.task-item.task-status-0:hover,
.task-item.task-status-3:hover,
.task-item.task-status-4:hover,
.task-item.task-status-5:hover {
    .task-opt {
        display: inline;
    }
}
.block-on-failure-tag {
    margin-left: 10px;
}
.terminal-menu-item {
    margin-bottom: 12px;
}
.terminal-menu-item + .terminal-menu-item {
    margin-left: 12px;
    margin-bottom: 12px;
}
.terminal-buttons {
    display: block;
    width: fit-content;
    margin: 0 auto;
    padding-top: 12px;
}
.config-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 20px;
    padding-right: 20px;
}
:deep(.main-dialog) {
    --el-dialog-padding-primary: 16px 16px 0 16px;
    .el-dialog__body {
        margin-top: 16px;
    }
}
:deep(.ba-terminal-dialog) {
    --el-dialog-width: 46% !important;
    .el-loading-spinner {
        --el-loading-spinner-size: 20px;
    }
}
@media screen and (max-width: 768px) {
    :deep(.ba-terminal-dialog) {
        --el-dialog-width: 80% !important;
    }
}
@media screen and (max-width: 540px) {
    :deep(.ba-terminal-dialog) {
        --el-dialog-width: 94% !important;
    }
}
</style>
