interface Window {
    existLoading: boolean
    lazy: NodeJS.Timer
    unique: number
    tokenRefreshing: boolean
    requests: Function[]
    eventSource: EventSource
}

interface anyObj {
    [key: string]: any
}

interface TableDefaultData<T = any> {
    list: T
    remark: string
    total: number
}

interface ApiResponse<T = any> {
    code: number
    data: T
    msg: string
    time: number
}

type ApiPromise<T = any> = Promise<ApiResponse<T>>
