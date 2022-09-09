export enum moduleInstallState {
    UNINSTALLED,
    INSTALLED,
    WAIT_INSTALL,
    CONFLICT_PENDING,
    DEPENDENT_WAIT_INSTALL,
    DIRECTORY_OCCUPIED,
    DISABLE,
}

export interface moduleInfo {
    uid: string
    title: string
    version: string
    state: number
    fullreload: number
    website: string
    stateTag: {
        type: string
        text: string
    }
}

export interface moduleState {
    loading: {
        buy: boolean
        table: boolean
        common: boolean
        install: boolean
        goodsInfo: boolean
    }
    dialog: {
        buy: boolean
        common: boolean
        goodsInfo: boolean
        baAccount: boolean
    }
    table: {
        remark: string
        modules: anyObj
        modulesEbak: anyObj
        category: anyObj
        onlyLocal: boolean
        indexLoaded: boolean
        params: anyObj
    }
    goodsInfo: anyObj
    buy: {
        info: anyObj
        agreement: boolean
    }
    common: {
        uid: string
        moduleState: number
        type: 'loading' | 'InstallConflict' | 'done' | 'waitFullReload' | 'disableConfirmConflict'
        dialogTitle: string
        fileConflict: anyObj[]
        dependConflict: anyObj[]
        loadingTitle: 'init' | 'download' | 'install' | 'wait-full-reload'
        loadingComponentKey: string
        waitInstallDepend: string[]
        dependInstallState: 'none' | 'executing' | 'success' | 'fail'
        disableConflictFile: { file: string }[]
        disableDependConflict: boolean
        disableParams: anyObj
    }
    installedModule: moduleInfo[]
    installedModuleUids: string[]
}

export const MODULE_TEMP = 'moduleTemp' // 模块安装/禁用状态临时记录
export const VITE_FULL_RELOAD = 'viteFullReload' // 是否触发了vite热重载的临时记录
