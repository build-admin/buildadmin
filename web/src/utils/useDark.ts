import { useDark, useToggle } from '@vueuse/core'
import { useConfig } from '/@/stores/config'

const isDark = useDark({
    onChanged(dark: boolean) {
        const config = useConfig()
        const htmlEl = document.getElementsByTagName('html')[0]
        if (dark) {
            htmlEl.setAttribute('class', 'dark')
        } else {
            htmlEl.setAttribute('class', '')
        }
        config.setLayout('isDark', dark)
        config.onSetLayoutColor()
    },
})
const toggleDark = useToggle(isDark)

export default toggleDark
