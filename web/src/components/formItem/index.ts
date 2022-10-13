import type { CSSProperties } from 'vue'
import type { FormItemRule } from 'element-plus'

export interface FormItemAttr {
    id?: string
    class?: string
    prop?: string | string[]
    'label-width'?: string | number
    required?: boolean
    rules?: FormItemRule | FormItemRule[]
    error?: string
    'show-message'?: boolean
    'inline-message'?: boolean
    size?: 'large' | 'default' | 'small'
    style?: CSSProperties
    'block-help'?: string
}
