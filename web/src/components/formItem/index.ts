import type { CSSProperties } from 'vue'
import type { FormItemRule } from 'element-plus'

export interface FormItemAttr {
    id?: string
    class?: string
    prop?: string | string[]
    labelWidth?: string | number
    required?: boolean
    rules?: FormItemRule | FormItemRule[]
    error?: string
    showMessage?: boolean
    inlineMessage?: boolean
    size?: 'large' | 'default' | 'small'
    style?: CSSProperties
    blockHelp?: string
}
