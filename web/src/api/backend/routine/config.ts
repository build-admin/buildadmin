import createAxios from '/@/utils/axios'

export const url = '/admin/routine.Config/'

export const actionUrl = new Map([
    ['index', url + 'index'],
    ['add', url + 'add'],
    ['edit', url + 'edit'],
    ['del', url + 'del'],
    ['sendTestMail', url + 'sendTestMail'],
])

export function index() {
    return createAxios({
        url: actionUrl.get('index'),
        method: 'get',
    })
}

export function postData(action: string, data: anyObj) {
    return createAxios(
        {
            url: actionUrl.get(action),
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function del(ids: string[]) {
    return createAxios(
        {
            url: actionUrl.get('del'),
            method: 'DELETE',
            params: {
                ids: ids,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function postSendTestMail(data: anyObj, mail: string) {
    return createAxios(
        {
            url: actionUrl.get('sendTestMail'),
            method: 'POST',
            data: Object.assign({}, data, { testMail: mail }),
        },
        {
            showSuccessMessage: true,
        }
    )
}
