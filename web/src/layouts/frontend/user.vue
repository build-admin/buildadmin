<template>
    <component :is="memberCenter.state.layoutMode"></component>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { initialize } from '/@/api/frontend/index'
import { getFirstRoute, routePush } from '/@/utils/router'
import { memberCenterBaseRoute } from '/@/router/static'
import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router'
import Default from '/@/layouts/frontend/container/default.vue'
import Disable from '/@/layouts/frontend/container/disable.vue'
import { ElNotification } from 'element-plus'
import { useI18n } from 'vue-i18n'
import userMounted from '/@/components/mixins/userMounted'
import { isEmpty } from 'lodash-es'

defineOptions({
    components: { Default, Disable },
})

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()

onBeforeRouteUpdate((to) => {
    memberCenter.setActiveRoute(to)
})

onMounted(async () => {
    const ret = await userMounted()
    if (ret.type == 'break') return
    if (ret.type == 'reload') return (window.location.href = ret.url)

    if (!userInfo.token) return router.push({ name: 'userLogin' })

    /**
     * 会员中心初始化请求，获取会员中心菜单信息等
     */

    const callback = () => {
        if (ret.type == 'jump') return router.push(ret.url)

        // 预跳转到上次路径
        if (route.params.to) {
            const lastRoute = JSON.parse(route.params.to as string)
            if (lastRoute.path != memberCenterBaseRoute.path) {
                let query = !isEmpty(lastRoute.query) ? lastRoute.query : {}
                routePush({ path: lastRoute.path, query: query })
                return
            }
        }

        // 跳转到第一个菜单
        if (route.name == 'userMainLoading') {
            let firstRoute = getFirstRoute(memberCenter.state.viewRoutes)
            if (firstRoute) {
                router.push({ path: firstRoute.path })
            } else {
                ElNotification({
                    type: 'error',
                    message: t('No route found to jump~'),
                })
            }
        }
    }

    if (siteConfig.userInitialize) {
        callback()
    } else {
        initialize(callback, true)
    }

    if (document.body.clientWidth < 1024) {
        memberCenter.setShrink(true)
    } else {
        memberCenter.setShrink(false)
    }
})
</script>

<style scoped lang="scss"></style>
