<template>
    <el-footer class="footer">
        <div>
            Copyright @ 2020~2022 {{ siteConfig.site_name }} 版权所有 <a href="http://beian.miit.gov.cn/">{{ siteConfig.record_number }}</a>
        </div>
    </el-footer>
</template>

<script setup lang="ts">
import { useSiteConfig } from '/@/stores/siteConfig'

const siteConfig = useSiteConfig()
</script>

<style scoped lang="scss">
.footer {
    position: fixed;
    display: flex;
    bottom: 0;
    width: 100%;
    justify-content: center;
    align-items: center;
    background-color: var(--color-sub-1);
    a {
        color: var(--color-secondary);
    }
}
</style>
