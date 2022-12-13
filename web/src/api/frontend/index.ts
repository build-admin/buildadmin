import createAxios from '/@/utils/axios'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { setTitle } from '/@/utils/common'

const controllerUrl = '/api/index/'

export function index() {
    const siteConfig = useSiteConfig()
    const memberCenter = useMemberCenter()

    if (siteConfig.siteName) {
        return
    }

    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    }).then((res) => {
        setTitle(res.data.site.siteName)
        siteConfig.dataFill(res.data.site)
        memberCenter.setStatus(res.data.openMemberCenter)
        if (!res.data.openMemberCenter) memberCenter.setLayoutMode('Disable')
    })
}
