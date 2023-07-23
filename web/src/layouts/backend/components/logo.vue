<template>
    <div class="layout-logo">
        <img v-if="!config.layout.menuCollapse" class="logo-img" src="~assets/logo.png" alt="logo" />
        <div v-if="!config.layout.menuCollapse" :style="{ color: config.getColorVal('menuActiveColor') }" class="website-name">
            {{ siteConfig.siteName }}
        </div>
        <Icon
            v-if="config.layout.layoutMode != 'Streamline'"
            @click="onMenuCollapse"
            :name="config.layout.menuCollapse ? 'fa fa-indent' : 'fa fa-dedent'"
            :class="config.layout.menuCollapse ? 'unfold' : ''"
            :color="config.getColorVal('menuActiveColor')"
            size="18"
            class="fold"
        />
    </div>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import { useSiteConfig } from '/@/stores/siteConfig'
import { closeShade } from '/@/utils/pageShade'
import { Session } from '/@/utils/storage'
import { BEFORE_RESIZE_LAYOUT } from '/@/stores/constant/cacheKey'
import { setNavTabsWidth } from '/@/utils/layout'

const config = useConfig()
const siteConfig = useSiteConfig()

const onMenuCollapse = function () {
    if (config.layout.shrink && !config.layout.menuCollapse) {
        closeShade()
    }

    config.setLayout('menuCollapse', !config.layout.menuCollapse)

    Session.set(BEFORE_RESIZE_LAYOUT, {
        layoutMode: config.layout.layoutMode,
        menuCollapse: config.layout.menuCollapse,
    })

    // 等待侧边栏动画结束后重新计算导航栏宽度
    setTimeout(() => {
        setNavTabsWidth()
    }, 350)
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
    background: v-bind('config.layout.layoutMode != "Streamline" ?  config.getColorVal("menuTopBarBackground"):"transparent"');
}
.logo-img {
    width: 28px;
}
.website-name {
    display: block;
    width: 180px;
    padding-left: 4px;
    font-size: var(--el-font-size-extra-large);
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.fold {
    margin-left: auto;
}
.unfold {
    margin: 0 auto;
}
</style>
