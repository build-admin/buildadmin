import { createApp } from 'vue'
import App from './App.vue'
import { i18n } from '/@/lang/index'
import { directives } from '/@/utils/directives'
import pinia from '/@/stores/index'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

async function start() {
    const app = createApp(App)

    app.use(pinia)
    app.use(i18n)
    app.use(ElementPlus, { i18n: i18n.global.t })

    directives(app) // 指令

    app.mount('#app')
}
start()
