import createAxios from '/@/utils/axios'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { setTitle } from '/@/utils/common'

const controllerUrl = '/index.php/api/index/'

export function index() {
    const siteConfig = useSiteConfig()
    const memberCenter = useMemberCenter()

    if (siteConfig.$state.site_name) {
        return
    }

    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    }).then((res) => {
        setTitle(res.data.site.site_name)
        siteConfig.$state = res.data.site
        memberCenter.state.open = res.data.open_member_center
        if (!res.data.open_member_center) memberCenter.state.layoutMode = 'Disable'
    })
}
