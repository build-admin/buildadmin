/*
 * 静态路由
 */
import { RouteRecordRaw } from 'vue-router'

const title = (name: string): string => {
    return `pagesTitle.${name}`
}

const staticRoutes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: '/',
        component: () => import('/@/views/frontend/index.vue'),
        meta: {
            title: title('home'),
        },
    },
    {
        path: '/admin',
        name: 'admin',
        component: () => import('/@/layouts/backend/index.vue'),
        redirect: '/admin/dashboard',
        meta: {
            title: title('admin'),
        },
        children: [
            {
                path: 'iframe/:url',
                name: 'layoutIframe',
                component: () => import('/@/layouts/router-view/iframe.vue'),
                meta: {
                    title: '内嵌iframe',
                },
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('/@/views/backend/dashboard.vue'),
                meta: {
                    title: '控制台',
                },
            },
            {
                path: 'routine/adminInfo',
                name: 'routine/adminInfo',
                component: () => import('/@/views/backend/routine/adminInfo.vue'),
                meta: {
                    title: '我的资料',
                },
            },
            {
                path: 'routine/config/:id?',
                name: 'routine/config',
                component: () => import('/@/views/backend/routine/config.vue'),
                meta: {
                    title: '系统设置',
                },
            },
            {
                path: 'table',
                name: 'table',
                component: () => import('/@/views/backend/table.vue'),
                meta: {
                    title: '表格示例',
                },
            },
        ],
    },
    {
        path: '/admin/login',
        name: 'adminLogin',
        component: () => import('/@/views/backend/login.vue'),
        meta: {
            title: title('adminLogin'),
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
            title: title('notFound'),
        },
    },
    {
        path: '/401',
        name: 'noPower',
        component: () => import('/@/views/common/error/401.vue'),
        meta: {
            title: title('noPower'),
        },
    },
]

export default staticRoutes
