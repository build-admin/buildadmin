import createAxios from '/@/utils/axios'

export const url = '/admin/routine.AdminInfo/'

export const actionUrl = new Map([
    ['index', url + 'index'],
    ['edit', url + 'edit'],
    ['log', '/admin/auth.AdminLog/index'],
])

export function index() {
    return createAxios({
        url: actionUrl.get('index'),
        method: 'get',
    })
}

export function log(filter: anyObj = {}) {
    return createAxios<TableDefaultData>({
        url: actionUrl.get('log'),
        method: 'get',
        params: filter,
    })
}

export function postData(data: anyObj) {
    return createAxios(
        {
            url: actionUrl.get('edit'),
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
