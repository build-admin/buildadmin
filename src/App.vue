<template>
    <InstallCheck v-if="global.step == 'check'" />
    <BaseConfig v-if="global.step == 'base-config'" />
    <Done v-if="global.step == 'done'" />
    <manualInstall v-if="global.step == 'manual-install'" />
</template>

<script setup lang="ts">
import { global } from '/@/utils/globalVar'
import InstallCheck from './views/installCheck.vue'
import BaseConfig from './views/baseConfig.vue'
import Done from './views/installDone.vue'
import manualInstall from './views/manualInstall.vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
var langValue = window.localStorage.getItem('ba-lang') || 'zh-cn'
locale.value = langValue
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
</style>
