import createAxios from '/@/utils/axios'

export const url = '/admin/security.SensitiveDataLog/'

export function rollback(ids: string[]) {
    return createAxios(
        {
            url: url + 'rollback',
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
