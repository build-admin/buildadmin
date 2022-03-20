import createAxios from '/@/utils/axios'
import { authMenu } from '/@/api/controllerUrls'

export function getMenuRules() {
    return createAxios({
        url: authMenu + 'index',
        method: 'get',
    })
}
