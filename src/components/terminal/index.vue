<template>
    <el-dialog
        v-bind="$attrs"
        v-model="terminal.state.show"
        :title="t('terminal.Terminal') + ' - ' + terminal.state.packageManager"
        custom-class="ba-terminal-dialog"
        :append-to-body="true"
    >
        <el-timeline v-if="terminal.state.taskList.length">
            <el-timeline-item
                v-for="(item, idx) in terminal.state.taskList"
                class="task-item"
                :class="'task-status-' + item.status"
                :type="getTaskStatus(item.status)['statusType']"
                center
                :timestamp="item.createtime"
                placement="top"
            >
                <el-card>
                    <div>
                        <el-tag :type="(getTaskStatus(item.status)['statusType'] as '')">{{ getTaskStatus(item.status)['statusText'] }}</el-tag>
                        <el-tag
                            class="block-on-failure-tag"
                            v-if="(item.status == taskStatus.Failed || item.status == taskStatus.Unknown) && item.blockOnFailure"
                            type="warning"
                            >{{ t('terminal.Failure to execute this command will block the execution of the queue') }}</el-tag
                        >
                        <span class="command">{{ item.command }}</span>
                        <div class="task-opt">
                            <el-button
                                :title="t('Retry')"
                                v-if="item.status == taskStatus.Failed || item.status == taskStatus.Unknown"
                                size="small"
                                v-blur
                                type="warning"
                                :icon="RefreshRight"
                                circle
                                @click="terminal.retryTask(idx)"
                            />
                            <el-button @click="terminal.delTask(idx)" :title="t('delete')" size="small" v-blur type="danger" :icon="Delete" circle />
                        </div>
                    </div>
                    <template v-if="item.status != taskStatus.Waiting">
                        <div
                            v-if="item.status != taskStatus.Connecting && item.status != taskStatus.Executing"
                            @click="terminal.setTaskShowMessage(idx)"
                            class="toggle-message-display"
                        >
                            <span>{{ t('terminal.Command run log') }}</span>
                            <el-icon size="16" color="#909399">
                                <ArrowUp v-if="item.showMessage" />
                                <ArrowDown v-else />
                            </el-icon>
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
                            <div v-for="msg in item.message" class="message-item">{{ msg }}</div>
                        </div>
                    </template>
                </el-card>
            </el-timeline-item>
        </el-timeline>
        <el-empty v-else :image-size="80" :description="t('terminal.No mission yet')" />

        <el-button-group>
            <el-button class="terminal-menu-item" v-blur @click="terminal.addTaskPM('test-install', false)">{{
                t('terminal.Test command')
            }}</el-button>
            <el-button class="terminal-menu-item" v-blur @click="terminal.addTaskPM('web-install')">{{
                t('terminal.Install dependent packages')
            }}</el-button>
            <el-button class="terminal-menu-item" v-blur @click="webBuild()">{{ t('terminal.Republish') }}</el-button>
            <el-button class="terminal-menu-item" v-blur @click="terminal.addTask('version-view.npm', false)">npm -v</el-button>
            <el-button class="terminal-menu-item" v-blur @click="onSwitchPackageManager">{{ t('Switch package manager') }}</el-button>
            <el-button class="terminal-menu-item" v-blur @click="terminal.clearSuccessTask()">{{ t('terminal.Clean up task list') }}</el-button>
        </el-button-group>
    </el-dialog>

    <el-dialog
        @close="terminal.togglePackageManagerDialog(false)"
        v-model="terminal.state.showPackageManagerDialog"
        custom-class="ba-terminal-dialog"
        :title="t('Please select package manager')"
        center
    >
        <div class="indent-2">
            {{ t('Switch package manager title') }}
        </div>
        <template #footer>
            <div class="package-manager-dialog-footer">
                <el-button @click="changePackageManager('npm')">npm</el-button>
                <el-button @click="changePackageManager('cnpm')">cnpm</el-button>
                <el-button @click="changePackageManager('pnpm')">pnpm</el-button>
                <el-button @click="changePackageManager('yarn')">yarn</el-button>
                <el-button @click="changePackageManager('ni')">ni</el-button>
                <el-button @click="changePackageManager('none')">{{ t('I want to execute the command manually') }}</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { useTerminal } from '/@/stores/terminal'
import { useI18n } from 'vue-i18n'
import { taskStatus } from './constant'
import { ElMessageBox } from 'element-plus'
import { ArrowDown, ArrowUp, RefreshRight, Delete } from '@element-plus/icons-vue'
import { changePackageManager } from '/@/api/install/index'

const { t } = useI18n()
const terminal = useTerminal()

const getTaskStatus = (status: number) => {
    let statusText = t('terminal.unknown')
    let statusType = ''
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

const webBuild = () => {
    ElMessageBox.confirm(t('terminal.Are you sure you want to republish?'), t('Reminder'), {
        confirmButtonText: t('Confirm'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    }).then(() => {
        terminal.addTaskPM('web-build')
    })
}

const onSwitchPackageManager = () => {
    ElMessageBox.confirm(t('Setup will restart. Are you sure you want to switch package manager?'), t('Reminder'), {
        confirmButtonText: t('Confirm'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    }).then(() => {
        window.localStorage.clear()
        location.reload()
    })
}
</script>

<style scoped lang="scss">
.command {
    font-size: var(--el-font-size-large);
    font-weight: bold;
    margin-left: 10px;
}
.exec-message {
    color: #fff;
    font-size: 12px;
    line-height: 16px;
    padding: 6px;
    background-color: #424251;
    margin-top: 10px;
    min-height: 30px;
    max-height: 200px;
    overflow-x: hidden;
    overflow-y: auto;
    scrollbar-width: none;
    &::-webkit-scrollbar {
        width: 5px;
    }
    &::-webkit-scrollbar-thumb {
        background: #c8c9cc;
        border-radius: 4px;
        box-shadow: none;
        -webkit-box-shadow: none;
    }
    &::-webkit-scrollbar-track {
        background: #f5f5f5;
    }
    &:hover {
        &::-webkit-scrollbar-thumb:hover {
            background: #909399;
        }
    }
}
.toggle-message-display {
    padding-top: 10px;
    font-size: 13px;
    color: var(--color-secondary);
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
    margin-bottom: 10px;
}
</style>

<style lang="scss">
// dialog自定义类目深度选择器失效
.ba-terminal-dialog {
    width: 42% !important;
}
@media screen and (max-width: 768px) {
    .ba-terminal-dialog {
        width: 80% !important;
    }
}
@media screen and (max-width: 540px) {
    .ba-terminal-dialog {
        width: 94% !important;
    }
}
</style>
