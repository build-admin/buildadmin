<template>
    <div>
        <el-container class="is-vertical">
            <Header />
            <el-row justify="center">
                <el-col class="user-layouts" :span="16" :xs="24">
                    <Aside />
                    <el-main>Main</el-main>
                </el-col>
            </el-row>
            <Footer />
        </el-container>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useRouter } from 'vue-router'
import { useMemberCenter } from '/@/stores/memberCenter'
import { index } from '/@/api/frontend/user/index'
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
import Aside from '/@/layouts/frontend/components/aside.vue'

const router = useRouter()
const userInfo = useUserInfo()
const memberCenter = useMemberCenter()

onMounted(() => {
    if (!userInfo.token) return router.push({ name: 'userLogin' })

    index().then((res) => {
        userInfo.$state = res.data.userInfo
        memberCenter.state.viewRoutes = res.data.menus
        memberCenter.state.showHeadline = res.data.menus.length > 1 ? true : false

        // 注册路由
        // 选中第一个菜单
    })
})
</script>

<style scoped lang="scss">
.user-layouts {
    display: flex;
    padding-top: 15px;
}
</style>
