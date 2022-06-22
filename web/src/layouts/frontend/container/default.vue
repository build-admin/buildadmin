<template>
    <el-container class="is-vertical">
        <Header />
        <el-row justify="center">
            <el-col class="user-layouts" :span="16" :xs="24">
                <Aside />
                <Main />
            </el-col>
        </el-row>
        <Footer />
    </el-container>
</template>

<script setup lang="ts">
import Header from '/@/layouts/frontend/components/header.vue'
import Aside from '/@/layouts/frontend/components/aside.vue'
import Main from '/@/layouts/frontend/components/main.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
</script>

<style scoped lang="scss">
.user-layouts {
    display: flex;
    padding-top: 15px;
}
</style>
