import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { STORE_TERMINAL } from '/@/stores/constant/cacheKey'
import { Terminal } from '/@/stores/interface/index'
import { timeFormat } from '/@/components/table'

export const useTerminal = defineStore(
    'terminal',
    () => {
        const state: Terminal = reactive({
            show: false,
            showDot: false,
            taskList: [],
        })

        function toggle(val: boolean = !state.show) {
            state.show = val
            if (val) {
                toggleDot(false)
            }
        }

        function toggleDot(val: boolean = !state.showDot) {
            state.showDot = val
        }

        function setTaskStatus(idx: number, status: number) {
            state.taskList[idx].status = status
        }

        function setTaskShowMessage(idx: number, val: boolean = !state.taskList[idx].showMessage) {
            state.taskList[idx].showMessage = val
        }

        function addTaskMessage(idx: number, message: string) {
            if (!state.show) toggleDot(true)
            state.taskList[idx].message = state.taskList[idx].message.concat(message)
            // 开始连接网络执行该命令
        }

        function addTask(command: string) {
            let newTaskIdx = state.taskList.length - 1
            state.taskList = state.taskList.concat({
                createtime: timeFormat(),
                status: 0,
                command: command,
                message: [],
                showMessage: false,
            })
        }

        function delTask(idx: number) {
            if (state.taskList[idx].status != 1 && state.taskList[idx].status != 2) {
                state.taskList.splice(idx, 1)
            }
        }

        return { state, toggle, toggleDot, setTaskStatus, setTaskShowMessage, addTaskMessage, addTask, delTask }
    },
    {
        persist: {
            key: STORE_TERMINAL,
        },
    }
)
