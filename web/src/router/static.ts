import { RouteRecordRaw } from 'vue-router'

const pageTitle = (name: string): string => {
    return `pagesTitle.${name}`
}

/*
 * 静态路由
 */
const staticRoutes: Array<RouteRecordRaw> = [
    {
        // 首页
        path: '/',
        name: '/',
        component: () => import('/@/views/frontend/index.vue'),
        meta: {
            title: pageTitle('home'),
        },
    },
    {
        // 管理员登录页
        path: '/admin/login',
        name: 'adminLogin',
        component: () => import('/@/views/backend/login.vue'),
        meta: {
            title: pageTitle('adminLogin'),
        },
    },
    {
        // 会员登录页
        path: '/user/login',
        name: 'userLogin',
        component: () => import('/@/views/frontend/user/login.vue'),
        meta: {
            title: pageTitle('userLogin'),
        },
    },
    {
        path: '/:path(.*)*',
        redirect: '/404',
    },
    {
        // 404
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
        // 无权限访问
        path: '/401',
        name: 'noPower',
        component: () => import('/@/views/common/error/401.vue'),
        meta: {
            title: pageTitle('noPower'),
        },
    },
]

/*
 * 后台基础静态路由
 */
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
            component: () => import('/@/layouts/common/router-view/iframe.vue'),
            meta: {
                title: pageTitle('Embedded iframe'),
            },
        },
    ],
}

/*
 * 会员中心基础静态路由
 */
const userBaseRoute: RouteRecordRaw = {
    path: '/user',
    name: 'user',
    component: () => import('/@/layouts/frontend/user.vue'),
    meta: {
        title: pageTitle('User'),
    },
}

staticRoutes.push(adminBaseRoute)
staticRoutes.push(userBaseRoute)

export { staticRoutes, adminBaseRoute, userBaseRoute }
