import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/Modulesdev/'

export function dir(params: anyObj = {}) {
    return createAxios({
        url: controllerUrl + 'dir',
        method: 'get',
        params: params,
    })
}

export function modulePack(params: anyObj = {}) {
    return createAxios({
        url: controllerUrl + 'modulePack',
        method: 'get',
        params: params,
    })
}

export function moduleBackup(params: anyObj = {}) {
    return createAxios(
        {
            url: controllerUrl + 'moduleBackup',
            method: 'get',
            params: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function delModuleFile(params: anyObj = {}) {
    return createAxios({
        url: controllerUrl + 'delModuleFile',
        method: 'get',
        params: params,
    })
}

export function fileChange(params: anyObj = {}) {
    return createAxios({
        url: controllerUrl + 'fileChange',
        method: 'get',
        params: params,
    })
}

export function manage(data: anyObj = {}) {
    return createAxios(
        {
            url: controllerUrl + 'manage',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
