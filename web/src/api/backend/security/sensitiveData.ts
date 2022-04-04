import createAxios from '/@/utils/axios'
import { securitySensitiveData } from '/@/api/controllerUrls'

export function add() {
    return createAxios({
        url: securitySensitiveData + 'add',
        method: 'get',
    })
}

export function getFieldList(table: string) {
    return createAxios({
        url: securitySensitiveData + 'getFieldList',
        method: 'get',
        params: {
            table: table,
        },
    })
}
