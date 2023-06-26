import createAxios from '/@/utils/axios'

export const url = '/admin/security.SensitiveData/'

export function add() {
    return createAxios({
        url: url + 'add',
        method: 'get',
    })
}
