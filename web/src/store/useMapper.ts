import { computed } from 'vue'
import { mapState, mapGetters, createNamespacedHelpers, Mapper, Computed, MapperForState } from 'vuex'
import { useStore } from '/@/store'

export function useMapper(state: string[], mapFn: Mapper<Computed> & MapperForState) {
    const store = useStore()
    const storeDataFns = mapFn(state)
    const storeData: any = {}
    Object.keys(storeDataFns).forEach((fnKey) => {
        const fn = storeDataFns[fnKey].bind({ $store: store })
        storeData[fnKey] = computed(fn)
    })

    return storeData
}

export function useState(moduleName: string = '', state: string[]) {
    let mapperFn = moduleName.length > 0 ? createNamespacedHelpers(moduleName).mapState : mapState
    return useMapper(state, mapperFn)
}

export function useGetters(moduleName: string = '', getters: string[]) {
    let mapperFn = moduleName.length > 0 ? createNamespacedHelpers(moduleName).mapGetters : mapGetters
    return useMapper(getters, mapperFn)
}
