<template>
    <component :is="memberCenter.state.layoutMode"></component>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'
import { useMemberCenter } from '/@/stores/memberCenter'
import { index } from '/@/api/frontend/user/index'
import { handleMemberCenterRoute, getMenuPaths, pushFirstRoute } from '/@/utils/router'
import { memberCenterBaseRoute } from '/@/router/static'

const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const memberCenter = useMemberCenter()

onMounted(() => {
    if (!userInfo.token) return router.push({ name: 'userLogin' })

    index().then((res) => {
        userInfo.$state = res.data.userInfo
        if (res.data.menus) {
            let menuRule = handleMemberCenterRoute(res.data.menus)
            memberCenter.setViewRoutes(menuRule)
            memberCenter.setShowHeadline(res.data.menus.length > 1 ? true : false)

            // 预跳转到上次路径
            if (route.query && route.query.url && route.query.url != memberCenterBaseRoute.path) {
                // 检查路径是否有权限
                let menuPaths = getMenuPaths(menuRule)
                if (menuPaths.indexOf(route.query.url as string) !== -1) {
                    let query = JSON.parse(route.query.query as string)
                    router.push({ path: route.query.url as string, query: Object.keys(query).length ? query : {} })
                    return
                }
            }

            // 跳转到第一个菜单
            pushFirstRoute(memberCenter.state.viewRoutes)
        }
    })
})
</script>

<!-- 只有在 components 选项中的组件可以被动态组件使用-->
<script lang="ts">
import Default from '/@/layouts/frontend/container/default.vue'
export default {
    components: { Default },
}
</script>

<style scoped lang="scss"></style>
