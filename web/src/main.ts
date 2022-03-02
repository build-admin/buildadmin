import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { loadLang } from '/@/lang/index'
import { registerIcons } from '/@/utils/common'
import ElementPlus from 'element-plus'
import mitt from 'mitt'
import pinia from '/@/stores/index'

import 'element-plus/dist/index.css'
import '/@/styles/base.scss'

async function start() {
    const app = createApp(App)

    // 全局注册 icons
    registerIcons(app)

    app.use(router)
    app.use(pinia)

    // 全局语言包加载
    const i18n = await loadLang(app)
    app.use(ElementPlus, { i18n: i18n.global.t })

    app.mount('#app')

    app.config.globalProperties.eventBus = mitt()
}
start()
