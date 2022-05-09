import createAxios from '/@/utils/axios'

const controllerUrl = '/index.php/api/index/'

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}
