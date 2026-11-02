<template>
    <div class="layout-title-bar" :class="config.layout.menuTopBarCenter ? 'layout-title-bar-center' : ''">
        <img
            v-if="config.layout.menuCollapse || config.layout.menuTopBarLogo"
            :class="config.layout.menuCollapse ? 'collapse' : ''"
            src="~assets/logo.png"
            class="logo-img"
            alt="logo"
        />
        <div v-if="!config.layout.menuCollapse" class="website-name">
            {{ siteConfig.siteName }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import { useSiteConfig } from '/@/stores/siteConfig'

const config = useConfig()
const siteConfig = useSiteConfig()
</script>

<style scoped lang="scss">
.layout-title-bar {
    width: 100%;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    padding: 10px 20px;
    height: v-bind('config.layout.layoutMode == "Double" ?  "60px":"46px"');
    background: v-bind('config.layout.layoutMode == "Streamline" ?  "transparent":config.getColorVal("menuTopBarBackground")');
}
.layout-title-bar-center {
    justify-content: center;
}
.logo-img {
    width: 28px;
    &.collapse {
        margin: 0 auto;
    }
}
.website-name {
    display: block;
    padding: 0 2px;
    font-size: var(--el-font-size-extra-large);
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: v-bind('config.getColorVal("menuTopBarColor")');
}
</style>
