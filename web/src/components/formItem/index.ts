import type { CSSProperties } from 'vue'
import type { FormItemProps, ElTooltipProps } from 'element-plus'

export interface FormItemAttr extends Partial<Writeable<FormItemProps>> {
    // 通用属性名称的键入提示
    id?: string
    class?: string
    style?: CSSProperties
    // 块级输入帮助信息
    blockHelp?: string
    // 输入提示信息（使用 el-tooltip 渲染）
    tip?: string | Partial<ElTooltipProps>
}
