import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { STORE_COMMON } from '/@/stores/constant/cacheKey'
import { Common } from '/@/stores/interface'

export const useCommon = defineStore(
    'common',
    () => {
        const state: Common = reactive({
            step: 'check',
            showStartDialog: true,
        })

        function setStep(val: Common['step']) {
            state.step = val
        }

        function toggleStartDialog(val: boolean) {
            state.showStartDialog = val
        }
        return { state, setStep, toggleStartDialog }
    },
    {
        persist: {
            key: STORE_COMMON,
        },
    }
)
