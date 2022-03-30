<script lang="ts">
import { createVNode, resolveComponent, defineComponent, PropType, VNode } from 'vue'
import { inputTypes, modelValuePropsTypes, modelValueTypes } from '/@/components/baInput'
import Array from '/@/components/baInput/array.vue'

export default defineComponent({
    name: 'baInput',
    props: {
        // 输入框类型,支持的输入框见 inputTypes
        type: {
            type: String,
            required: true,
            validator: (value: string) => {
                return inputTypes.includes(value)
            },
        },
        // 双向绑定值
        modelValue: {
            type: modelValuePropsTypes,
            required: true,
        },
        // 输入框的附加属性
        attr: {
            type: Object as PropType<InputAttr>,
            default: () => {},
        },
        // 额外数据,radio、checkbox的选项等数据
        data: {
            type: Object as PropType<InputData>,
            default: () => {},
        },
    },
    setup(props, { emit }) {
        const onValueUpdate = (value: modelValueTypes) => {
            emit('update:modelValue', value)
        }

        // 子级元素属性
        let childrenAttr = props.data && props.data.childrenAttr ? props.data.childrenAttr : {}

        // 文本和数字输入框
        if (props.type == 'string' || props.type == 'number' || props.type == 'textarea') {
            return () =>
                createVNode(resolveComponent('el-input'), {
                    type: props.type == 'string' ? 'text' : props.type,
                    ...props.attr,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        } else if (props.type == 'radio' || props.type == 'checkbox') {
            // radio、checkbox
            let vNode: VNode[] = []
            for (const key in props.data.content) {
                vNode.push(
                    createVNode(
                        resolveComponent('el-' + props.type),
                        {
                            label: key,
                            ...childrenAttr,
                        },
                        () => props.data.content[key]
                    )
                )
            }
            return () =>
                createVNode(
                    resolveComponent('el-' + props.type + '-group'),
                    {
                        ...props.attr,
                        modelValue: props.modelValue,
                        'onUpdate:modelValue': onValueUpdate,
                    },
                    () => vNode
                )
        } else if (props.type == 'switch') {
            return () =>
                createVNode(resolveComponent('el-switch'), {
                    ...props.attr,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        } else if (props.type == 'datetime') {
            return () =>
                createVNode(resolveComponent('el-date-picker'), {
                    type: 'datetime',
                    'value-format': 'YYYY-MM-DD HH:mm:ss',
                    ...props.attr,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        } else if (props.type == 'select' || props.type == 'selects') {
            let vNode: VNode[] = []
            for (const key in props.data.content) {
                vNode.push(
                    createVNode(resolveComponent('el-option'), {
                        key: key,
                        label: key,
                        value: props.data.content[key],
                        ...childrenAttr,
                    })
                )
            }
            return () =>
                createVNode(
                    resolveComponent('el-select'),
                    {
                        multiple: props.type == 'select' ? false : true,
                        ...props.attr,
                        modelValue: props.modelValue,
                        'onUpdate:modelValue': onValueUpdate,
                    },
                    () => vNode
                )
        } else if (props.type == 'array') {
            return () =>
                createVNode(Array, {
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        } else {
            console.warn('暂不支持' + props.type + '的输入框类型，你可以自行在 BaInput 组件内添加逻辑')
        }
    },
})
</script>
