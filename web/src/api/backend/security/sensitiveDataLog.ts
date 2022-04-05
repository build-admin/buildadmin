import createAxios from '/@/utils/axios'
import { securitySensitiveDataLog } from '/@/api/controllerUrls'

export function rollback(ids: string[]) {
    return createAxios(
        {
            url: securitySensitiveDataLog + 'rollback',
            method: 'POST',
            data: {
                ids: ids,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function info(id: string) {
    return createAxios({
        url: securitySensitiveDataLog + 'info',
        method: 'get',
        params: {
            id: id,
        },
    })
}
