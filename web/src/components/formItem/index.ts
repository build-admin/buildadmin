import type { CSSProperties } from 'vue'
import type { FormItemProps } from 'element-plus'

export interface FormItemAttr extends Partial<FormItemProps> {
    id?: string
    class?: string
    style?: CSSProperties
    blockHelp?: string
}
