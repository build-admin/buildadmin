<template>
    <div v-loading="true" element-loading-background="#f5f5f5" element-loading-text="加载中..." class="default-main ba-main-loading"></div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ba-main-loading {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
