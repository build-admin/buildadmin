<template>
    <div class="layout-logo">
        <img v-if="!layoutConfig.menuCollapse" class="logo-img" src="~assets/logo.png" alt="logo" />
        <div v-if="!layoutConfig.menuCollapse" :style="{ color: layoutConfig.menuActiveColor }" class="website-name">BuildAdmin</div>
        <Icon
            v-if="layoutConfig.layoutMode != 'Streamline'"
            @click="onMenuCollapse"
            :name="layoutConfig.menuCollapse ? 'fa fa-indent' : 'fa fa-dedent'"
            :class="layoutConfig.menuCollapse ? 'unfold' : ''"
            :color="layoutConfig.menuActiveColor"
            size="18"
            class="fold"
        />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from '/@/store'
import { closeShade } from '/@/utils/pageShade'

const store = useStore()
const layoutConfig = computed(() => store.state.config.layout)

const onMenuCollapse = function () {
    if (layoutConfig.value.shrink && !layoutConfig.value.menuCollapse) {
        closeShade()
    }

    store.commit('config/setAndCache', {
        name: 'layout.menuCollapse',
        value: !layoutConfig.value.menuCollapse,
    })
}
</script>

<style scoped lang="scss">
.layout-logo {
    width: 100%;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    padding: 10px;
    background: v-bind('layoutConfig.layoutMode != "Streamline" ? layoutConfig.menuTopBarBackground:"transparent"');
}
.logo-img {
    width: 28px;
}
.website-name {
    padding-left: 4px;
    font-size: var(--el-font-size-extra-large);
    font-weight: 600;
}
.fold {
    margin-left: auto;
}
.unfold {
    margin: 0 auto;
}
</style>
