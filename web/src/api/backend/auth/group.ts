import createAxios from '/@/utils/axios'
import { adminRule } from '/@/api/controllerUrls'

export function getAdminRules() {
    return createAxios({
        url: adminRule + 'index',
        method: 'get',
    })
}
