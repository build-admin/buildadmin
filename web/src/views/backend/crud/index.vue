<template>
    <div>
        <component :is="state.step"></component>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import Start from '/@/views/backend/crud/start.vue'
import Design from '/@/views/backend/crud/design.vue'
import { state } from '/@/views/backend/crud/index'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
onMounted(() => {
    if (import.meta.hot) {
        import.meta.hot.on('vite:beforeFullReload', () => {
            throw t('This is a deliberate error thrown to prevent a hot update of Vite')
        })
        import.meta.hot.on('vite:beforeUpdate', () => {
            throw t('This is a deliberate error thrown to prevent a hot update of Vite')
        })
        import.meta.hot.on('vite:beforePrune', () => {
            throw t('This is a deliberate error thrown to prevent a hot update of Vite')
        })
    }
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'crud/crud',
    components: { Start, Design },
})
</script>

<style scoped lang="scss"></style>
