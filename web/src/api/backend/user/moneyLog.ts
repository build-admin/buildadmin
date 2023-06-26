import createAxios from '/@/utils/axios'

export const url = '/admin/user.MoneyLog/'

export function add(userId: string) {
    return createAxios({
        url: url + 'add',
        method: 'get',
        params: {
            userId: userId,
        },
    })
}
