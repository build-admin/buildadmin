<template>
    <el-header v-if="!tabFullScreen" class="layout-header">
        <component :is="layoutMode + 'NavBar'"></component>
    </el-header>
</template>
<script setup lang="ts">
import { computed } from 'vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import DefaultNavBar from './navBar/default.vue'
import ClassicNavBar from './navBar/classic.vue'
import StreamlineNavBar from './menuHorizontal.vue'

const config = useConfig()
const navTabs = useNavTabs()
const tabFullScreen = computed(() => navTabs.state.tabFullScreen)
const layoutMode = computed(() => config.layout.layoutMode)
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
