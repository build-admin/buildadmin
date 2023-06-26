import createAxios from '/@/utils/axios'

export const url = '/admin/crud.Crud/'

export function generate(data: anyObj) {
    return createAxios(
        {
            url: url + 'generate',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function getDatabaseList() {
    return createAxios({
        url: url + 'databaseList',
        method: 'get',
    })
}

export function getFileData(table: string, commonModel = 0) {
    return createAxios({
        url: url + 'getFileData',
        method: 'get',
        params: {
            table: table,
            commonModel: commonModel,
        },
    })
}

export function generateCheck(data: anyObj) {
    return createAxios(
        {
            url: url + 'generateCheck',
            method: 'post',
            data: data,
        },
        {
            showCodeMessage: false,
        }
    )
}

export function parseFieldData(type: string, table = '', sql = '') {
    return createAxios({
        url: url + 'parseFieldData',
        method: 'post',
        data: {
            type: type,
            table: table,
            sql: sql,
        },
    })
}

export function postLogStart(id: number) {
    return createAxios({
        url: url + 'logStart',
        method: 'post',
        data: {
            id: id,
        },
    })
}

export function postDel(id: number) {
    return createAxios({
        url: url + 'delete',
        method: 'post',
        data: {
            id: id,
        },
    })
}
