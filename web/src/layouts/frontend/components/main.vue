<template>
    <el-main class="layout-main">
        <el-scrollbar class="layout-main-scrollbar" ref="mainScrollbarRef">
            <router-view v-slot="{ Component }">
                <component :is="Component" :key="Component" />
            </router-view>
        </el-scrollbar>
    </el-main>
</template>

<script setup lang="ts">

</script>

<style scoped lang="scss">

</style>
