<template>
    <el-dialog v-model="terminal.state.show" :title="t('Terminal')" width="42%">
        <el-timeline>
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
                        <span class="command">{{ item.command }}</span>
                        <div class="task-opt">
                            <el-button
                                :title="t('Retry')"
                                v-if="item.status == 4 || item.status == 5"
                                size="small"
                                v-blur
                                type="warning"
                                icon="el-icon-RefreshRight"
                                circle
                            />
                            <el-button @click="terminal.delTask(idx)" :title="t('delete')" size="small" v-blur type="danger" icon="el-icon-Delete" circle />
                        </div>
                    </div>
                    <template v-if="item.status != 0">
                        <div v-if="item.status != 1 && item.status != 2" @click="terminal.setTaskShowMessage(idx)" class="toggle-message-display">
                            <span>{{ t('Command run log') }}</span>
                            <Icon :name="item.showMessage ? 'el-icon-ArrowUp' : 'el-icon-ArrowDown'" size="16" color="#909399" />
                        </div>
                        <div v-if="item.status == 1 || item.status == 2 || (item.status > 2 && item.showMessage)" class="exec-message">
                            <el-scrollbar :ref="messageScrollbarRefs.set" class="exec-message-scrollbar">
                                <div v-for="msg in item.message" class="message-item">{{ msg }}</div>
                            </el-scrollbar>
                        </div>
                    </template>
                </el-card>
            </el-timeline-item>
        </el-timeline>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useTerminal } from '/@/stores/terminal'
import { useI18n } from 'vue-i18n'
import { useTemplateRefsList } from '@vueuse/core'

const { t } = useI18n()
const terminal = useTerminal()
const messageScrollbarRefs = useTemplateRefsList()

const getTaskStatus = (status: number) => {
    let statusText = '未知'
    let statusType = ''
    switch (status) {
        case 0:
            statusText = '等待执行'
            statusType = 'info'
            break
        case 1:
            statusText = '连接中...'
            statusType = 'warning'
            break
        case 2:
            statusText = '执行中...'
            statusType = 'warning'
            break
        case 3:
            statusText = '执行成功'
            statusType = 'success'
            break
        case 4:
            statusText = '执行失败'
            statusType = 'danger'
            break
        case 5:
            statusText = '执行结果未知'
            statusType = 'danger'
            break
    }
    return {
        statusText: statusText,
        statusType: statusType,
    }
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
    .exec-message-scrollbar {
        height: 200px;
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
</style>
