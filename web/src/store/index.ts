import { InjectionKey } from 'vue'
import { createStore, useStore as baseUseStore, Store } from 'vuex'
import { RootStateTypes } from '/@/store/interface/index'

const modulesFiles = import.meta.globEager('./modules/*.ts')
const pathList: string[] = []

for (const path in modulesFiles) {
    pathList.push(path)
}

const modules = pathList.reduce((modules: { [x: string]: any }, modulePath: string) => {
    const moduleName = modulePath.replace(/^\.\/modules\/(.*)\.\w+$/, '$1')
    const value = modulesFiles[modulePath]
    modules[moduleName] = value.default
    return modules
}, {})

export const key: InjectionKey<Store<RootStateTypes>> = Symbol()

export const store = createStore<RootStateTypes>({ modules })

export function useStore() {
    return baseUseStore(key)
}
