<template>
    <div>
        <component :is="state.step"></component>
    </div>
</template>

<script setup lang="ts">
import { onActivated, onDeactivated, onUnmounted, onMounted } from 'vue'
import Start from '/@/views/backend/crud/start.vue'
import Design from '/@/views/backend/crud/design.vue'
import { state } from '/@/views/backend/crud/index'
import { closeHotUpdate, openHotUpdate } from '/@/utils/vite'

defineOptions({
    name: 'crud/crud',
    components: { Start, Design },
})

onMounted(() => {
    closeHotUpdate('crud')
})

onActivated(() => {
    closeHotUpdate('crud')
})

onDeactivated(() => {
    openHotUpdate('crud')
})

onUnmounted(() => {
    openHotUpdate('crud')
})
</script>

<style scoped lang="scss"></style>
