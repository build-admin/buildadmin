import createAxios from '/@/utils/axios'
const controllerUrl = '/admin/routine.config/'
export const actionUrl = new Map([
    ['index', controllerUrl + 'index'],
    ['add', controllerUrl + 'add'],
    ['edit', controllerUrl + 'edit'],
    ['del', controllerUrl + 'del'],
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
            url: controllerUrl + 'sendTestMail',
            method: 'POST',
            data: Object.assign({}, data, { testMail: mail }),
        },
        {
            showSuccessMessage: true,
        }
    )
}
