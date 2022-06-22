<template>
    <el-main class="layout-main">
        <el-scrollbar class="layout-main-scrollbar" ref="mainScrollbarRef">
            <router-view v-slot="{ Component }">
                <component :is="Component" :key="Component" />
            </router-view>
        </el-scrollbar>
    </el-main>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.layout-container .layout-main {
    padding: 0 !important;
    overflow: hidden;
    width: 100%;
    height: 100%;
}
.layout-main-scrollbar {
    width: 100%;
    position: relative;
    overflow: hidden;
}
</style>
