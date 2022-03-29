import createAxios from '/@/utils/axios'
import { userScoreLog } from '/@/api/controllerUrls'

export function add(userId: string) {
    return createAxios({
        url: userScoreLog + 'add',
        method: 'get',
        params: {
            userId: userId,
        },
    })
}
