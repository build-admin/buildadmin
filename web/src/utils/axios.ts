import axios from 'axios'
import { computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Local } from '/@/utils/storage'
import { store } from '/@/store/index'
import { isProd } from '/@/utils/vite'

const getUrl = (): string => {
    const value: string = import.meta.env.VITE_AXIOS_BASE_URL as string
    return value == 'getCurrentDomain' ? window.location.protocol + '//' + window.location.host : value
}

const lang = computed(() => store.state.config.defaultLang)

const Axios = axios.create({
    baseURL: getUrl(),
    timeout: 50000,
    headers: {
        'Content-Type': 'application/json',
        'think-lang': lang.value,
    },
})

export default Axios
