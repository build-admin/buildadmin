interface Window {
    existLoading: boolean
    lazy: NodeJS.Timer
    unique: number
    tokenRefreshing: boolean
    requests: Function[]
}

interface InputData {
    // 标题
    title?: string
    // 内容,比如radio的选项列表数据 content: { a: '选项1', b: '选项2' }
    content?: any
    // 提示信息
    tip?: string
    // 需要生成子级元素时,子级元素属性(比如radio)
    childrenAttr?: anyObj
    // 城市选择器等级,1=省,2=市,3=区
    level?: number
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

interface ApiPromise<T = any> extends Promise<ApiResponse<T>> {}
