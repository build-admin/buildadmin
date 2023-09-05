import createAxios from '/@/utils/axios'

export const url = '/admin/Dashboard/'

export function index() {
    return createAxios({
        url: url + 'index',
        method: 'get',
    })
}
