import createAxios from '/@/utils/axios'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { debounce, setTitleFromRoute } from '/@/utils/common'
import { handleFrontendRoute } from '/@/utils/router'
import { useUserInfo } from '/@/stores/userInfo'
import router from '/@/router/index'
import { isEmpty } from 'lodash-es'

export const indexUrl = '/api/index/'

/**
 * 前台初始化请求，获取站点配置信息，动态路由信息等
 * 1. 首次初始化携带了会员token时，一共只初始化一次
 * 2. 首次初始化未带会员token，将在登录后再初始化一次
 */
export function initialize(callback?: (res: ApiResponse) => void, requiredLogin?: boolean) {
    debounce(() => {
        if (router.currentRoute.value.meta.initialize === false) return

        const userInfo = useUserInfo()
        const siteConfig = useSiteConfig()
        if (!userInfo.isLogin() && siteConfig.initialize) return
        if (userInfo.isLogin() && siteConfig.userInitialize) return

        const memberCenter = useMemberCenter()

        createAxios({
            url: indexUrl + 'index',
            method: 'get',
            params: {
                requiredLogin: requiredLogin ? 1 : 0,
            },
        }).then((res) => {
            handleFrontendRoute(res.data.rules, res.data.menus)
            siteConfig.dataFill(res.data.site)
            memberCenter.setStatus(res.data.openMemberCenter)

            if (!isEmpty(res.data.userInfo)) {
                res.data.userInfo.refresh_token = userInfo.getToken('refresh')
                userInfo.dataFill(res.data.userInfo)

                // 请求到会员信息才设置会员中心初始化是成功的
                siteConfig.setUserInitialize(true)
            }

            if (!res.data.openMemberCenter) memberCenter.setLayoutMode('Disable')

            siteConfig.setInitialize(true)

            // 根据当前路由重设页面标题
            setTitleFromRoute()

            typeof callback == 'function' && callback(res)
        })
    }, 200)()
}
