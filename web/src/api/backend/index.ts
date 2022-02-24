import Axios from '/@/utils/axios'

export function login(params: object) {
    return Axios.request({
        url: '/index.php/admin/index/login',
        method: 'post',
        data: params
    })
}
