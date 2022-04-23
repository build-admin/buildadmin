import { Component } from 'vue'
import type { FormItemRule } from 'element-plus'
declare global {
    /**
     * input可用attr,用于代码提示,渲染不同输入组件时,需要的attr是不一样的
     * https://element-plus.org/zh-CN/component/input.html#input-属性
     */
    interface InputAttr {
        id?: string
        name?: string
        type?: string
        placeholder?: string
        maxlength?: string | number
        minlength?: string | number
        'show-word-limit'?: boolean
        clearable?: boolean
        'show-password'?: boolean
        disabled?: boolean
        size?: 'large' | 'default' | 'small'
        'prefix-icon'?: string | Component
        'suffix-icon'?: string | Component
        rows?: number
        border?: boolean
        autosize?: boolean | anyObj
        autocomplete?: string
        readonly?: boolean
        max?: string | number
        min?: string | number
        step?: string | number
        resize?: 'none' | 'both' | 'horizontal' | 'vertical'
        autofocus?: boolean
        form?: string
        label?: string
        tabindex?: string | number
        'validate-event'?: boolean
        'input-style'?: anyObj
        // DateTimePicker属性
        editable?: boolean
        'start-placeholder'?: string
        'end-placeholder'?: string
        'time-arrow-control'?: boolean
        format?: string
        'popper-class'?: string
        'range-separator'?: string
        'default-value'?: Date
        'default-time'?: Date | Date[]
        'value-format'?: string
        'unlink-panels'?: boolean
        'clear-icon'?: string | Component
        shortcuts?: { text: string; value: Date | Function }[]
        disabledDate?: Function
        cellClassName?: Function
        teleported?: boolean
        // select属性
        multiple?: boolean
        'value-key'?: string
        'collapse-tags'?: string
        'collapse-tags-tooltip'?: boolean
        'multiple-limit'?: number
        effect?: 'dark' | 'light'
        filterable?: boolean
        'allow-create'?: boolean
        'filter-method'?: Function
        remote?: false // 禁止使用远程搜索,如需使用请使用单独封装好的 remoteSelect 组件
        'remote-method'?: false
        'no-match-text'?: string
        'no-data-text'?: string
        'reserve-keyword'?: boolean
        'default-first-option'?: boolean
        'popper-append-to-body'?: boolean
        persistent?: boolean
        'automatic-dropdown'?: boolean
        'fit-input-width'?: boolean
        'tag-type'?: 'success' | 'info' | 'warning' | 'danger'
        params?: anyObj
        // 远程select属性
        field?: string
        'remote-url'?: string
        // 图标选择器属性
        'show-icon-name'?: boolean
        placement?: string
        title?: string
        // 图片文件上传属性
        action?: string
        headers?: anyObj
        method?: string
        data?: anyObj
        'with-credentials'?: boolean
        'show-file-list'?: boolean
        drag?: boolean
        accept?: boolean
        'list-type'?: string
        'auto-upload'?: boolean
        limit?: number
        onPreview?: Function
        onRemove?: Function
        onSuccess?: Function
        onError?: Function
        onProgress?: Function
        onExceed?: Function
        onBeforeUpload?: Function
        onBeforeRemove?: Function
        onChange?: Function
        onInput?: Function
        onVisibleChange?: Function
        onRemoveTag?: Function
        onClear?: Function
        onBlur?: Function
        onFocus?: Function
        onCalendarChange?: Function
        onPanelChange?: Function
    }

    interface FormItemAttr {
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
    }
}
