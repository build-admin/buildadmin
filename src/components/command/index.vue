<template>
    <div v-show="state.minimize" class="command">
        <div class="header">
            <img draggable="false" class="logo" :src="logo" alt="BuildAdmin Logo" />
            <div class="title">终端</div>
            <div v-if="state.stateMessage.value" class="state-message" :class="'text-' + state.stateMessage.type">{{ state.stateMessage.value }}</div>
            <div class="header-opt">
                <div @click="onMinimize" class="opt-item">
                    <img src="~assets/img/minimize.png" title="缩小" alt="缩小" />
                </div>
                <!-- <div class="opt-item">
                    <img src="~assets/img/config.png" title="设置" alt="设置" />
                </div> -->
            </div>
        </div>
        <div class="exec-message">
            <div class="message-item" v-for="(item, idx) in state.executionMessage">{{ item }}</div>
        </div>
    </div>
    <div v-show="!state.minimize" @click="state.minimize = true" class="minimize">
        <img draggable="false" class="logo" :src="logo" alt="BuildAdmin Logo" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, watch } from 'vue'
import { isProd } from '/@/utils/vite'
import logo from '/@/assets/img/logo.svg'
import { popenWindowUrl } from '/@/api/install/index'
var source: EventSource, timer: NodeJS.Timer, oldCompleteNarrow: boolean

interface Props {
    commandKey: string
    width?: string
    height?: string
    top?: string
    right?: string
    bottom?: string
    left?: string
    zIndex?: number
    // 执行完成后缩小窗口
    completeNarrow?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    commandKey: '',
    width: '460px',
    height: '300px',
    top: '10px',
    right: '10px',
    bottom: 'unset',
    left: 'unset',
    zIndex: 99990,
    completeNarrow: true,
})

const emits = defineEmits<{
    (e: 'callback', data: any): void
}>()

const state = reactive({
    minimize: true,
    stateMessage: { type: 'warning', value: props.commandKey ? '连接中...' : '无命令' },
    executionMessage: [],
    completeNarrow: props.completeNarrow,
})

const onMinimize = () => {
    state.minimize = false
    state.completeNarrow = false
}

const startEventSource = (commandKey: string) => {
    const eventSourceurl = isProd(process.env.NODE_ENV)
        ? window.location.protocol + '//' + window.location.host
        : (import.meta.env.VITE_AXIOS_BASE_URL as string)

    state.stateMessage = { type: 'warning', value: '连接中...' }

    source = new EventSource(eventSourceurl + popenWindowUrl + '?command=' + commandKey)
    source.onmessage = function (e) {
        if (e.data == 'link-successful') {
            state.stateMessage = { type: 'success', value: '连接成功 正在执行 ' + commandKey }
        } else if (e.data == 'command-execution-completed') {
            source.close()
            state.stateMessage = { type: 'warning', value: commandKey + ' 已执行' }
            if (state.completeNarrow) {
                state.minimize = false
            }
            // 确定 completed 事件于 success 之后发送
            setTimeout(() => {
                emits('callback', {
                    commandKey: commandKey,
                    value: 'command-execution-completed',
                })
            }, 1000)
        } else if (e.data) {
            if (e.data == commandKey + '-success') {
                emits('callback', {
                    commandKey: commandKey,
                    value: 'success',
                })
            } else {
                state.executionMessage = state.executionMessage.concat(e.data)
            }
        }
    }
}

onMounted(() => {
    if (props.commandKey) {
        startEventSource(props.commandKey)
    } else {
        state.minimize = false
    }
})

// 支持命令切换,最多保存一条待执行命令
watch(
    () => props.commandKey,
    (newVal) => {
        if (!newVal) {
        } else if (!source || (source && source.readyState == 2)) {
            state.minimize = true
            startEventSource(newVal)
        } else {
            state.stateMessage = { type: 'warning', value: newVal + ' 等待执行' }
            oldCompleteNarrow = state.completeNarrow
            state.completeNarrow = false
            timer = setInterval(() => {
                if (!source || (source && source.readyState == 2)) {
                    state.minimize = true
                    startEventSource(newVal)
                    clearInterval(timer)
                    state.completeNarrow = oldCompleteNarrow
                }
            }, 1000)
        }
    }
)
</script>

<style scoped lang="scss">
.minimize {
    position: fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    right: 40px;
    bottom: 100px;
    height: 30px;
    width: 30px;
    background-color: #3f6ad8;
    border-radius: 50%;
    padding: 5px;
    cursor: pointer;
    user-select: none;
    animation: pulse 2s infinite;
    img {
        padding-top: 4px;
    }
}
.command {
    position: fixed;
    width: v-bind('props.width');
    height: v-bind('props.height');
    top: v-bind('props.top');
    right: v-bind('props.right');
    bottom: v-bind('props.bottom');
    left: v-bind('props.left');
    background-color: #000000;
    z-index: v-bind('props.zIndex');
    .header {
        display: flex;
        align-items: center;
        height: 30px;
        background-color: #ffffff;
        padding: 0 0 0 10px;
        .logo {
            height: 14px;
            width: 14px;
            padding-top: 4px;
            margin-right: 5px;
        }
        .state-message {
            font-size: 13px;
            padding-left: 5px;
        }
        .header-opt {
            margin-left: auto;
            display: flex;
            align-items: center;
            .opt-item {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 30px;
                width: 30px;
                cursor: pointer;
                img {
                    height: 18px;
                    width: 18px;
                }
                &:hover {
                    background-color: #f5f5f5;
                }
            }
        }
    }
    .exec-message {
        color: #fff;
        font-size: 13px;
        padding: 6px;
        height: calc(100% - 42px);
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
}
.text-success {
    color: #67c23a;
}
.text-warning {
    color: #e6a23c;
}
.text-danger {
    color: #f56c6c;
}

@media screen and (max-width: 768px) {
    .command {
        width: 92vw;
    }
}

@-webkit-keyframes pulse {
    0% {
        -webkit-box-shadow: 0 0 0 0 rgba(13, 130, 255, 0.4);
    }
    70% {
        -webkit-box-shadow: 0 0 0 10px rgba(13, 130, 255, 0);
    }
    100% {
        -webkit-box-shadow: 0 0 0 0 rgba(13, 130, 255, 0);
    }
}

@keyframes pulse {
    0% {
        -moz-box-shadow: 0 0 0 0 rgba(13, 130, 255, 0.4);
        box-shadow: 0 0 0 0 rgba(13, 130, 255, 0.4);
    }
    70% {
        -moz-box-shadow: 0 0 0 10px rgba(13, 130, 255, 0);
        box-shadow: 0 0 0 10px rgba(13, 130, 255, 0);
    }
    100% {
        -moz-box-shadow: 0 0 0 0 rgba(13, 130, 255, 0);
        box-shadow: 0 0 0 0 rgba(13, 130, 255, 0);
    }
}
</style>
