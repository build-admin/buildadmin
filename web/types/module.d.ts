/// <reference types="vite/client" />

declare module '*.vue' {
    import { DefineComponent } from 'vue'
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type
    const component: DefineComponent<{}, {}, any>
    export default component
}
