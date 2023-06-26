<template>
    <el-dialog v-bind="$attrs" v-model="terminal.state.show" :title="t('terminal.Terminal')" class="ba-terminal-dialog" :append-to-body="true">
        <el-alert class="terminal-warning-alert" v-if="state.terminalWarning" :title="state.terminalWarning" type="error" />
        <el-timeline v-if="terminal.state.taskList.length">
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

        <el-button-group>
            <el-button class="terminal-menu-item" icon="el-icon-MagicStick" v-blur @click="terminal.addTaskPM('test', false)">
                {{ t('terminal.Test command') }}
            </el-button>
            <el-button class="terminal-menu-item" icon="el-icon-Download" v-blur @click="terminal.addTaskPM('web-install')">
                {{ t('terminal.Install dependent packages') }}
            </el-button>
            <el-button class="terminal-menu-item" icon="el-icon-Sell" v-blur @click="webBuild()">{{ t('terminal.Republish') }}</el-button>
            <el-button v-if="!state.menuExpand" class="terminal-menu-item" icon="el-icon-Expand" v-blur @click="state.menuExpand = true"></el-button>
            <template v-else>
                <el-button class="terminal-menu-item" icon="el-icon-Delete" v-blur @click="terminal.clearSuccessTask()">
                    {{ t('terminal.Clean up task list') }}
                </el-button>
                <el-button class="terminal-menu-item" icon="el-icon-Switch" v-blur @click="terminal.togglePackageManagerDialog(true)">
                    {{ t('terminal.Package manager') }} {{ terminal.state.packageManager.toUpperCase() }}
                </el-button>
                <el-button class="terminal-menu-item" icon="el-icon-Tools" v-blur @click="terminal.toggleConfigDialog()">
                    {{ t('terminal.Terminal settings') }}
                </el-button>
            </template>
        </el-button-group>
    </el-dialog>

    <el-dialog
        @close="terminal.togglePackageManagerDialog(false)"
        :model-value="terminal.state.showPackageManagerDialog"
        class="ba-terminal-dialog"
        :title="t('terminal.Please select package manager')"
        center
    >
        <div class="indent-2">
            {{ t('terminal.Switch package manager title') }}
        </div>
        <template #footer>
            <div class="package-manager-dialog-footer">
                <el-button @click="changePackageManager('npm')">npm</el-button>
                <el-button @click="changePackageManager('cnpm')">cnpm</el-button>
                <el-button @click="changePackageManager('pnpm')">pnpm</el-button>
                <el-button @click="changePackageManager('yarn')">yarn</el-button>
                <el-button @click="changePackageManager('ni')">ni</el-button>
                <el-button @click="changePackageManager('none')">{{ t('terminal.I want to execute the command manually') }}</el-button>
            </div>
        </template>
    </el-dialog>

    <el-dialog
        @close="terminal.toggleConfigDialog(false)"
        :model-value="terminal.state.showConfig"
        class="ba-terminal-dialog"
        :title="t('terminal.Terminal settings')"
    >
        <el-form label-position="top">
            <FormItem
                :label="t('terminal.Install service port')"
                v-model="state.port"
                type="number"
                :input-attr="{ onChange: onChangePort }"
                :placeholder="
                    t('terminal.The port number to start the installation service (this port needs to be opened for external network access)')
                "
            />
            <FormItem
                :label="t('terminal.Installation service startup command')"
                v-model="startCommand"
                type="string"
                :input-attr="{ disabled: true }"
                :attr="{ 'block-help': t('terminal.Please execute this command to start the service (add Su under Linux)') }"
            />
            <FormItem
                :label="t('terminal.Installation service URL')"
                v-model="serviceURL"
                type="string"
                :input-attr="{ disabled: true }"
                :attr="{ 'block-help': t('terminal.Please access the site through the installation service URL (except in debug mode)') }"
            />
        </el-form>
        <FormItem
            :label="t('terminal.Clean up successful tasks when starting a new task')"
            :model-value="terminal.state.automaticCleanupTask"
            type="radio"
            :data="{ content: { '0': t('Disable'), '1': t('Enable') }, childrenAttr: { border: true } }"
            :input-attr="{ onChange: terminal.changeAutomaticCleanupTask }"
        />
        <div class="config-buttons">
            <el-button @click="terminal.toggleConfigDialog(false)">{{ t('terminal.Back to terminal') }}</el-button>
        </div>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, computed, watch, onMounted } from 'vue'
import { useTerminal } from '/@/stores/terminal'
import { useI18n } from 'vue-i18n'
import { taskStatus } from './constant'
import { ElMessageBox, TimelineItemProps } from 'element-plus'
import { postChangeTerminalConfig } from '/@/api/common'
import FormItem from '/@/components/formItem/index.vue'
import { getUrlPort } from '/@/utils/axios'

const { t } = useI18n()
const terminal = useTerminal()

const state = reactive({
    terminalWarning: '',
    port: terminal.state.port,
    menuExpand: document.documentElement.clientWidth > 1840 ? true : false,
})

const startCommand = computed(() => {
    let tempPort = terminal.state.port == '' ? '80' : terminal.state.port
    return tempPort == '8000' ? 'php think run' : 'php think run -p ' + tempPort
})
const serviceURL = computed(() => {
    let tempPort = terminal.state.port == '' ? '' : ':' + terminal.state.port
    return 'http://localhost' + tempPort + ' ' + t('terminal.or') + ' http://' + t('terminal.Site domain name') + tempPort
})

/**
 * 发送网络请求修改端口号
 */
const onChangePort = (val: string) => {
    postChangeTerminalConfig({ port: val })
        .then((res) => {
            if (res.code == 1) {
                terminal.changePort(val)
                checkPort()
            } else {
                state.port = terminal.state.port
            }
        })
        .catch(() => {
            state.port = terminal.state.port
        })
}

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

const webBuild = () => {
    ElMessageBox.confirm(t('terminal.Are you sure you want to republish?'), t('Reminder'), {
        confirmButtonText: t('Confirm'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    }).then(() => {
        terminal.addTaskPM('web-build')
    })
}

const changePackageManager = (val: string) => {
    postChangeTerminalConfig({ manager: val }).then((res) => {
        if (res.code == 1) {
            terminal.changePackageManager(val)
        }
    })
    terminal.togglePackageManagerDialog(false)
}

const setTerminalWarning = (warning: string) => {
    state.terminalWarning = warning
}

const checkPort = () => {
    if (getUrlPort() != terminal.state.port) {
        setTerminalWarning(t('terminal.The current terminal is not running under the installation service, and some commands may not be executed'))
    } else {
        setTerminalWarning('')
    }
}

watch(
    () => terminal.state.port,
    (newVal) => {
        if (newVal != state.port) {
            state.port = newVal
            checkPort()
        }
    }
)

onMounted(() => {
    checkPort()
})
</script>

<style scoped lang="scss">
.terminal-warning-alert {
    margin: -20px 0 20px 0;
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
    scrollbar-width: none;
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
    margin-bottom: 10px;
}
.config-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 20px;
    padding-right: 20px;
}
</style>

<style lang="scss">
// dialog自定义类目深度选择器失效
.ba-terminal-dialog {
    width: 42%;
}
@media screen and (max-width: 768px) {
    .ba-terminal-dialog {
        width: 80%;
    }
}
@media screen and (max-width: 540px) {
    .ba-terminal-dialog {
        width: 94%;
    }
}
</style>
