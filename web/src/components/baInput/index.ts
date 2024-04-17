import type { Component, CSSProperties } from 'vue'

/**
 * 支持的输入框类型
 * 若您正在设计数据表，可以找到 ./helper.ts 文件来参考对应类型的：数据字段设计示例
 */
export const inputTypes = [
    'string',
    'password',
    'number',
    'radio',
    'checkbox',
    'switch',
    'textarea',
    'array',
    'datetime',
    'year',
    'date',
    'time',
    'select',
    'selects',
    'remoteSelect',
    'remoteSelects',
    'editor',
    'city',
    'image',
    'images',
    'file',
    'files',
    'icon',
    'color',
]
export type modelValueTypes = string | number | boolean | object

export interface InputData {
    // 标题
    title?: string
    // 内容,比如radio的选项列表数据 content: { a: '选项1', b: '选项2' }
    content?: any
    // 提示信息
    tip?: string
    // 需要生成子级元素时,子级元素属性(比如radio)
    childrenAttr?: anyObj
    // 城市选择器等级,1=省,2=市,3=区
    level?: number
}

/**
 * input可用属性,用于代码提示,渲染不同输入组件时,需要的属性是不一样的
 * https://element-plus.org/zh-CN/component/input.html#input-属性
 */
export interface InputAttr {
    id?: string
    name?: string
    type?: string
    placeholder?: string
    maxlength?: string | number
    minlength?: string | number
    showWordLimit?: boolean
    clearable?: boolean
    showPassword?: boolean
    disabled?: boolean
    size?: 'large' | 'default' | 'small'
    prefixIcon?: string | Component
    suffixIcon?: string | Component
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
    validateEvent?: boolean
    inputStyle?: anyObj
    // DateTimePicker属性
    editable?: boolean
    startPlaceholder?: string
    endPlaceholder?: string
    timeArrowControl?: boolean
    format?: string
    popperClass?: string
    rangeSeparator?: string
    defaultValue?: Date
    defaultTime?: Date | Date[]
    valueFormat?: string
    unlinkPanels?: boolean
    clearIcon?: string | Component
    shortcuts?: { text: string; value: Date | Function }[]
    disabledDate?: Function
    cellClassName?: Function
    teleported?: boolean
    // select属性
    multiple?: boolean
    valueKey?: string
    collapseTags?: string
    collapseTagsTooltip?: boolean
    multipleLimit?: number
    effect?: 'dark' | 'light'
    filterable?: boolean
    allowCreate?: boolean
    filterMethod?: Function
    remote?: false // 禁止使用远程搜索,如需使用请使用单独封装好的 remoteSelect 组件
    remoteMethod?: false
    labelFormatter?: (optionData: anyObj, optionKey: string) => string
    noMatchText?: string
    noDataText?: string
    reserveKeyword?: boolean
    defaultFirstOption?: boolean
    popperAppendToBody?: boolean
    persistent?: boolean
    automaticDropdown?: boolean
    fitInputWidth?: boolean
    tagType?: 'success' | 'info' | 'warning' | 'danger'
    params?: anyObj
    // 远程select属性
    pk?: string
    field?: string
    remoteUrl?: string
    tooltipParams?: anyObj
    // 图标选择器属性
    showIconName?: boolean
    placement?: string
    title?: string
    // 颜色选择器
    showAlpha?: boolean
    colorFormat?: string
    predefine?: string[]
    // 图片文件上传属性
    action?: string
    headers?: anyObj
    method?: string
    data?: anyObj
    withCredentials?: boolean
    showFileList?: boolean
    drag?: boolean
    accept?: string
    listType?: string
    autoUpload?: boolean
    limit?: number
    hideSelectFile?: boolean
    returnFullUrl?: boolean
    forceLocal?: boolean
    // editor属性
    height?: string
    mode?: string
    editorStyle?: CSSProperties
    style?: CSSProperties
    toolbarConfig?: anyObj
    editorConfig?: anyObj
    editorType?: string
    preview?: boolean
    language?: string
    theme?: 'light' | 'dark'
    toolbarsExclude?: string[]
    fileForceLocal?: boolean
    // array组件属性
    keyTitle?: string
    valueTitle?: string
    // 返回数据类型
    dataType?: string
    // 是否渲染为 button（radio 和 checkbox）
    button?: boolean
    // 事件
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
    onActiveChange?: Function
    onRow?: Function
    [key: string]: any
}

/**
 * Input 支持的类型对应的数据字段设计数据
 */
export interface FieldData {
    [key: string]: {
        type: string // 数据类型
        length: number // 长度
        precision: number // 小数点
        default: string // 默认值
        null: boolean // 允许 null
        primaryKey: boolean // 主键
        unsigned: boolean // 无符号
        autoIncrement: boolean // 自动递增
    }
}
