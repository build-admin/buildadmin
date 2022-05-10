/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/ban-types
    const component: DefineComponent<{}, {}, any>
    export default component
}

interface ApiResponse<T = any> {
    code: number
    data: T
    msg: string
    time: number
}

interface ApiPromise<T = any> extends Promise<ApiResponse<T>> {}

interface anyObj {
    [key:string]:any
}

interface Window {
    eventSource: EventSource
}
