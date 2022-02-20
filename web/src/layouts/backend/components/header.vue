<template>
    <el-header v-if="!tabFullScreen" class="layout-header">
        <component :is="layoutMode + 'NavBar'"></component>
    </el-header>
</template>
<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from '/@/store'
import DefaultNavBar from './navBar/default.vue'
import ClassicNavBar from './navBar/classic.vue'
import StreamlineNavBar from './menuHorizontal.vue'

const store = useStore()
const tabFullScreen = computed(() => store.state.navTabs.tabFullScreen)
const layoutMode = computed(() => store.state.config.layout.layoutMode)
</script>
<script lang="ts">
export default {
    components: { DefaultNavBar, ClassicNavBar, StreamlineNavBar },
}
</script>

<style scoped lang="scss">
.layout-header {
    height: auto;
    padding: 0;
}
</style>
