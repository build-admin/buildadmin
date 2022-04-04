import createAxios from '/@/utils/axios'
import { securityDataRecycleLog } from '/@/api/controllerUrls'

export function restore(ids: string[]) {
    return createAxios(
        {
            url: securityDataRecycleLog + 'restore',
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
