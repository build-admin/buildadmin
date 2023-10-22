import type { RouteRecordRaw } from 'vue-router'

/**
 * 会员中心基础路由路径
 */
export const memberCenterBaseRoutePath = '/user'

/*
 * 会员中心基础静态路由
 */
const memberCenterBaseRoute: RouteRecordRaw = {
    path: memberCenterBaseRoutePath,
    name: 'user',
    component: () => import('/@/layouts/frontend/user.vue'),
    // 重定向到 loading 路由
    redirect: memberCenterBaseRoutePath + '/loading',
    meta: {
        title: `pagesTitle.user`,
    },
    children: [
        {
            path: 'loading/:to?',
            name: 'userMainLoading',
            component: () => import('/@/layouts/common/components/loading.vue'),
            meta: {
                title: `pagesTitle.loading`,
            },
        },
    ],
}

export default memberCenterBaseRoute
