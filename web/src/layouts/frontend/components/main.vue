<template>
    <el-main class="layout-main">
        <router-view v-slot="{ Component }">
            <transition :name="config.layout.mainAnimation" mode="out-in">
                <component :is="Component" />
            </transition>
        </router-view>
    </el-main>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'

const config = useConfig()
</script>

<style scoped lang="scss">
.layout-main {
    padding: 0 !important;
    overflow-x: hidden;
}
</style>
