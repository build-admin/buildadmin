export enum moduleInstallState {
    UNINSTALLED,
    INSTALLED,
    WAIT_INSTALL,
    CONFLICT_PENDING,
    DEPENDENT_WAIT_INSTALL,
    DIRECTORY_OCCUPIED,
    DISABLE,
}

export interface moduleState {
    tableLoading: boolean
    remark: string
    modules: anyObj
    modulesEbak: anyObj
    category: anyObj
    params: anyObj
    goodsInfo: {
        showDialog: boolean
        info: anyObj
        loading: boolean
        showConfirmConflict: boolean
        conflictFile: {
            file: string
        }[]
        dependConflict: boolean
    }
    showBaAccount: boolean
    buy: {
        showLoading: boolean
        showDialog: boolean
        info: anyObj
        agreement: boolean
    }
    publicButtonLoading: boolean
    install: {
        showDialog: boolean
        title: string
        loading: boolean
        stateTitle: string
        fileConflict: any[]
        dependConflict: any[]
        uid: string
        state: number
        componentKey: string
        waitInstallDepend: anyObj
        dependInstallState: 'executing' | 'success' | 'fail'
    }
    onlyLocal: boolean
    loadIndex: boolean
    installedModule: {
        uid: string
        state: number
        version: string
        website: string
    }[]
    installedModuleUids: number[]
    waitFullReload: boolean
    moduleDisableParams: anyObj
}

export const INSTALL_MODULE_TEMP = 'installModuleTemp' // 模块安装状态临时记录
export const VITE_FULL_RELOAD = 'viteFullReload' // 是否触发了vite热重载的临时记录
export const DEPEND_DATA_TEMP = 'dependDataTemp' // 依赖更新临时记录
