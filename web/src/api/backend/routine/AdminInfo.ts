import createAxios from '/@/utils/axios'
import { authAdminLog } from '/@/api/controllerUrls'

const controllerUrl = '/index.php/admin/routine.AdminInfo/'
export const actionUrl = new Map([
    ['index', controllerUrl + 'index'],
    ['edit', controllerUrl + 'edit'],
    ['log', authAdminLog + 'index'],
])

export function index() {
    return createAxios({
        url: actionUrl.get('index'),
        method: 'get',
    })
}

export function log(filter: anyObj = {}): ApiPromise<TableDefaultData> {
    return createAxios({
        url: actionUrl.get('log'),
        method: 'get',
        params: filter,
    }) as ApiPromise
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
