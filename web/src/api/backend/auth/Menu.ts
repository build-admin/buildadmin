import createAxios from '/@/utils/axios'

export const indexUrl = '/index.php/admin/auth.menu/index'
export const editUrl = '/index.php/admin/auth.menu/edit'

export function index(loading: boolean) {
    return createAxios(
        {
            url: indexUrl,
            method: 'get',
        },
        {
            loading: loading,
        }
    )
}

export function edit(params: anyObj) {
    return createAxios({
        url: editUrl,
        method: 'get',
        params: params,
    })
}

export function postEdit(data: anyObj) {
    return createAxios({
        url: editUrl,
        method: 'post',
        data: data,
    })
}
