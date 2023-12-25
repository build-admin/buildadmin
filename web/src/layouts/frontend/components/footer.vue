<template>
    <el-footer class="footer">
        <div>
            Copyright @ 2020~{{ new Date().getFullYear() }} {{ siteConfig.siteName }} {{ $t('Copyright') }}
            <a href="http://beian.miit.gov.cn/">{{ siteConfig.recordNumber }}</a>
        </div>
    </el-footer>
</template>

<script setup lang="ts">
import { useSiteConfig } from '/@/stores/siteConfig'

const siteConfig = useSiteConfig()
</script>

<style scoped lang="scss">
.footer {
    display: flex;
    width: 100%;
    justify-content: center;
    align-items: center;
    background-color: var(--el-color-info-light-7);
    a {
        color: var(--el-text-color-secondary);
    }
    @media screen and (max-width: 768px) {
        a {
            display: block;
            text-align: center;
        }
    }
}
</style>
