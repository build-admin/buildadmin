import createAxios from '/@/utils/axios'
import { userMoneyLog } from '/@/api/controllerUrls'

export function add(userId: string) {
    return createAxios({
        url: userMoneyLog + 'add',
        method: 'get',
        params: {
            userId: userId,
        },
    })
}
