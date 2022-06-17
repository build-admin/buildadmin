<template>
    <el-container class="layout-container">
        <Aside />
        <el-container class="content-wrapper">
            <Header />
            <Main />
        </el-container>
    </el-container>
    <CloseFullScreen v-if="navTabs.state.tabFullScreen" />
</template>

<script setup lang="ts">
import Aside from '/@/layouts/backend/components/aside.vue'
import Header from '/@/layouts/backend/components/header.vue'
import Main from '/@/layouts/backend/router-view/main.vue'
import CloseFullScreen from '/@/layouts/backend/components/closeFullScreen.vue'
import { useNavTabs } from '/@/stores/navTabs'
const navTabs = useNavTabs()
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
