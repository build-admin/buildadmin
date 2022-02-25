import Axios from '/@/utils/axios'

export function login(params: object, method: 'get'|'post') {
    return Axios.request({
        url: '/index.php/admin/index/login',
        method: method,
        data: params
    })
}
