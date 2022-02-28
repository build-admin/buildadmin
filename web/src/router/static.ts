/*
 * 静态路由
 */
import { RouteRecordRaw } from 'vue-router'

const pageTitle = (name: string): string => {
    return `pagesTitle.${name}`
}

const staticRoutes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: '/',
        component: () => import('/@/views/frontend/index.vue'),
        meta: {
            title: pageTitle('home'), // 首页
        },
    },
    {
        path: '/admin/login',
        name: 'adminLogin',
        component: () => import('/@/views/backend/login.vue'),
        meta: {
            title: pageTitle('adminLogin'), // 管理员登录页
        },
    },
    {
        path: '/:path(.*)*',
        redirect: '/404',
    },
    {
        path: '/404',
        name: 'notFound',
        component: () => import('/@/views/common/error/404.vue'),
        meta: {
            title: pageTitle('notFound'), // 页面不存在
        },
    },
    {
        // 后台找不到页面了-可能是路由未加载上
        path: '/admin:path(.*)*',
        redirect: (to) => {
            return { name: 'adminMainLoading', query: { url: to.path, query: JSON.stringify(to.query) } }
        },
    },
    {
        path: '/401',
        name: 'noPower',
        component: () => import('/@/views/common/error/401.vue'),
        meta: {
            title: pageTitle('noPower'), // 无权限访问
        },
    },
]

// 后台基础路由和静态路由
const adminBaseRoute: RouteRecordRaw = {
    path: '/admin',
    name: 'admin',
    component: () => import('/@/layouts/backend/index.vue'),
    redirect: '/admin/loading',
    meta: {
        title: pageTitle('admin'),
    },
    children: [
        {
            path: 'loading',
            name: 'adminMainLoading',
            component: () => import('/@/views/backend/loading.vue'),
            meta: {
                title: pageTitle('adminMainLoading'),
            },
        },
        {
            path: 'iframe/:url',
            name: 'layoutIframe',
            component: () => import('/@/layouts/router-view/iframe.vue'),
            meta: {
                title: pageTitle('Embedded iframe'),
            },
        },
    ],
}

staticRoutes.push(adminBaseRoute)

export { staticRoutes, adminBaseRoute }
