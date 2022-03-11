<template>
    <div>
        <div v-loading="true" element-loading-background="#f5f5f5" element-loading-text="加载中..." class="default-main ba-main-loading"></div>
        <div v-if="state.showReload" class="loading-footer">
            <el-button @click="refresh" type="warning">重新加载</el-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onUnmounted, reactive } from 'vue'
import router from '/@/router/index'
let timer: NodeJS.Timer

const state = reactive({
    maximumWait: 1000 * 10,
    showReload: false,
})

const refresh = () => {
    router.go(0)
}

timer = setTimeout(() => {
    state.showReload = true
}, state.maximumWait)

onUnmounted(() => {
    clearTimeout(timer)
})
</script>

<style scoped lang="scss">
.ba-main-loading {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.loading-footer {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
