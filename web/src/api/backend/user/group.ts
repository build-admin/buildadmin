import createAxios from '/@/utils/axios'
import { userRule } from '/@/api/controllerUrls'

export function getUserRules() {
    return createAxios({
        url: userRule + 'index',
        method: 'get',
    })
}
