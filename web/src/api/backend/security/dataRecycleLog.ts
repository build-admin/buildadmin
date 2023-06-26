import createAxios from '/@/utils/axios'

export const url = '/admin/security.DataRecycleLog/'

export function restore(ids: string[]) {
    return createAxios(
        {
            url: url + 'restore',
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
        url: url + 'info',
        method: 'get',
        params: {
            id: id,
        },
    })
}
