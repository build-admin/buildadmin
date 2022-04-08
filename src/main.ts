import { createApp } from 'vue'
import App from './App.vue'
import { i18n } from '/@/lang/index'
import { directives } from '/@/utils/directives'

const app = createApp(App)

directives(app) // 指令

app.use(i18n)
app.mount('#app')
