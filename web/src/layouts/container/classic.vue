<template>
    <el-container class="layout-container">
        <Aside />
        <el-container class="content-wrapper">
            <Header />
            <Main />
        </el-container>
    </el-container>
    <CloseFullScreen v-if="tabFullScreen" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Aside from '../backend/components/aside.vue'
import Header from '../backend/components/header.vue'
import Main from '/@/layouts/router-view/main.vue'
import CloseFullScreen from '../backend/components/closeFullScreen.vue'
import { useStore } from '/@/store/index'

const store = useStore()
const tabFullScreen = computed(() => store.state.navTabs.tabFullScreen)
</script>

<style scoped>
.layout-container {
    height: 100%;
    width: 100%;
}
.content-wrapper {
    flex-direction: column;
    width: 100%;
    height: 100%;
}
</style>
