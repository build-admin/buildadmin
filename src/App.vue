<template>
    <el-config-provider :locale="lang">
        <Check v-if="common.state.step == 'check'" />
        <Config v-if="common.state.step == 'config'" />
        <Done v-if="common.state.step == 'done'" />
        <ManualInstall v-if="common.state.step == 'manualInstall'" />

        <Terminal />
        <div class="ba-terminal">
            <el-badge :is-dot="terminal.state.showDot">
                <img @click="terminal.toggle()" class="terminal-logo" draggable="false" :src="logo" alt="BuildAdmin Logo" />
            </el-badge>
        </div>
    </el-config-provider>
</template>

<script setup lang="ts">
import Terminal from '/@/components/terminal/index.vue'
import { useI18n } from 'vue-i18n'
import { useCommon } from '/@/stores/common'
import { useTerminal } from '/@/stores/terminal'
import logo from '/@/assets/img/logo.svg'
import Check from '/@/views/check.vue'
import Config from '/@/views/config.vue'
import Done from '/@/views/done.vue'
import ManualInstall from '/@/views/manualInstall.vue'

const common = useCommon()
const terminal = useTerminal()
const { locale, getLocaleMessage } = useI18n()

var langValue = window.localStorage.getItem('ba-lang') || 'zh-cn'
locale.value = langValue

const lang = getLocaleMessage(langValue) as any
</script>

<style>
html,
body,
#app {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    font-family: Helvetica Neue, Helvetica, PingFang SC, Hiragino Sans GB, Microsoft YaHei, SimSun, sans-serif;
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    -webkit-tap-highlight-color: transparent;
    background-color: #f5f5f5;
    font-size: 14px;
    overflow-y: auto;
    position: relative;
}
.slide-bottom-enter-active,
.slide-bottom-leave-active {
    will-change: transform;
    transition: all 0.6s ease;
}
.slide-bottom-enter-from {
    opacity: 0;
    transform: translateY(-30px);
}
.slide-bottom-leave-to {
    opacity: 0;
    transform: translateY(30px);
}
.ba-terminal {
    position: fixed;
    right: 40px;
    bottom: 200px;
    height: 40px;
    width: 40px;
    user-select: none;
    border-radius: 50%;
    cursor: pointer;
    animation: pulse 2s infinite;
}
.terminal-logo {
    height: 38px;
    width: 38px;
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
