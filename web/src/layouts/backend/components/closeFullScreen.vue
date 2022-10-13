<template>
    <div :title="$t('layouts.Exit full screen')" @mouseover.stop="onMouseover" @mouseout.stop="onMouseout">
        <div @click.stop="onCloseFullScreen" class="close-full-screen" :style="{ top: state.closeBoxTop + 'px' }">
            <Icon name="el-icon-Close" />
        </div>
        <div class="close-full-screen-on"></div>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { useNavTabs } from '/@/stores/navTabs'

const navTabs = useNavTabs()

const state = reactive({
    closeBoxTop: 20,
})
onMounted(() => {
    setTimeout(() => {
        state.closeBoxTop = -30
    }, 300)
})
/*
 * 鼠标滑到顶部显示关闭全屏按钮
 * 要检查 hover 的元素在外部，直接使用事件而不是css
 */
const onMouseover = () => {
    state.closeBoxTop = 20
}
const onMouseout = () => {
    state.closeBoxTop = -30
}
const onCloseFullScreen = () => {
    navTabs.setFullScreen(false)
}
</script>

<style scoped lang="scss">
.close-full-screen {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    right: calc(50% - 20px);
    z-index: 9999999;
    height: 40px;
    width: 40px;
    background-color: rgba($color: #000000, $alpha: 0.1);
    border-radius: 50%;
    box-shadow: var(--el-box-shadow-light);
    transition: all 0.3s ease;
    .icon {
        color: rgba($color: #000000, $alpha: 0.6) !important;
    }
    &:hover {
        background-color: rgba($color: #000000, $alpha: 0.3);
        .icon {
            color: rgba($color: #ffffff, $alpha: 0.6) !important;
        }
    }
}
.close-full-screen-on {
    position: fixed;
    top: 0;
    z-index: 9999998;
    height: 60px;
    width: 100px;
    left: calc(50% - 50px);
}
</style>
