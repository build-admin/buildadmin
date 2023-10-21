import { getCurrentInstance } from 'vue'
import type { ComponentInternalInstance } from 'vue'

export default function useCurrentInstance() {
    if (!getCurrentInstance()) {
        throw new Error('useCurrentInstance() can only be used inside setup() or functional components!')
    }
    const { appContext } = getCurrentInstance() as ComponentInternalInstance
    const proxy = appContext.config.globalProperties
    return {
        proxy,
    }
}
