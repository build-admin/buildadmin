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
        quickClose: boolean
        type: 'loading' | 'InstallConflict' | 'done' | 'disableConfirmConflict' | 'uploadInstall'
        disableHmr: boolean
        dialogTitle: string
        fileConflict: anyObj[]
        dependConflict: anyObj[]
        loadingTitle: 'init' | 'download' | 'install'
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
