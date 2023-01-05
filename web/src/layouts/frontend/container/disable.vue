<template>
    <el-container class="is-vertical">
        <Header />
        <el-scrollbar :style="layoutMainScrollbarStyle()" ref="mainScrollbarRef">
            <el-row class="frontend-footer-brother" justify="center">
                <el-col class="user-layouts" :span="16" :xs="24">
                    <el-alert :center="true" :title="$t('Member center disabled')" type="error" />
                </el-col>
            </el-row>
            <Footer />
        </el-scrollbar>
    </el-container>
</template>

<script setup lang="ts">
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
import { mainHeight as layoutMainScrollbarStyle } from '/@/utils/layout'
</script>

<style scoped lang="scss">
.user-layouts {
    display: flex;
    padding-top: 15px;
    align-items: flex-start;
}
@media screen and (max-width: 768px) {
    .user-layouts {
        padding-top: 0;
    }
}
</style>
