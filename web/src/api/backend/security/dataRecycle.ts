import createAxios from '/@/utils/axios'
import { securityDataRecycle } from '/@/api/controllerUrls'

export function add() {
    return createAxios({
        url: securityDataRecycle + 'add',
        method: 'get',
    })
}

export function getPk(table: string) {
    return createAxios({
        url: securityDataRecycle + 'getPk',
        method: 'get',
        params: {
            table: table,
        },
    })
}
