<template>
    <div>
        <component :is="state.step"></component>
    </div>
</template>

<script setup lang="ts">
import { onActivated, onDeactivated, onUnmounted } from 'vue'
import Start from '/@/views/backend/crud/start.vue'
import Design from '/@/views/backend/crud/design.vue'
import { state } from '/@/views/backend/crud/index'

defineOptions({
    name: 'crud/crud',
    components: { Start, Design },
})

onActivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:close-hot', { type: 'crud' })
})

onDeactivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'crud' })
})

onUnmounted(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'crud' })
})
</script>

<style scoped lang="scss"></style>
