<template>
    <component :is="memberCenter.state.layoutMode"></component>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useMemberCenter } from '/@/stores/memberCenter'
import { index } from '/@/api/frontend/user/index'
import { handleMemberCenterRoute, getMenuPaths, getFirstRoute } from '/@/utils/router'
import { memberCenterBaseRoute } from '/@/router/static'
import { ElNotification } from 'element-plus'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const memberCenter = useMemberCenter()

onBeforeRouteUpdate((to) => {
    memberCenter.setActiveRoute(to)
})

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
            let firstRoute = getFirstRoute(memberCenter.state.viewRoutes)
            if (firstRoute) {
                memberCenter.activateMenu(firstRoute)
            } else {
                ElNotification({
                    type: 'error',
                    message: t('No route found to jump~'),
                })
            }
        }
    })

    if (document.body.clientWidth < 1024) {
        memberCenter.setShrink(true)
    } else {
        memberCenter.setShrink(false)
    }
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
