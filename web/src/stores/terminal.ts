import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { STORE_TERMINAL } from '/@/stores/constant/cacheKey'

export const useTerminal = defineStore(
    'terminal',
    () => {
        const state = reactive({
            show: false,
            showDot: false,
        })

        function toggle(val: boolean = !state.show) {
            state.show = val
        }

        function toggleDot(val: boolean = !state.showDot) {
            state.showDot = val
        }

        return { state, toggle, toggleDot }
    },
    {
        persist: {
            key: STORE_TERMINAL,
        },
    }
)
