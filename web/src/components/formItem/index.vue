<script lang="ts">
import { createVNode, defineComponent, resolveComponent, PropType, computed } from 'vue'
import { inputTypes, modelValueTypes, InputAttr, InputData } from '/@/components/baInput'
import { FormItemAttr } from '/@/components/formItem'
import BaInput from '/@/components/baInput/index.vue'

export default defineComponent({
    name: 'formItem',
    props: {
        // el-form-item的label
        label: {
            type: String,
        },
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
            required: true,
        },
        // 输入框的附加属性
        inputAttr: {
            type: Object as PropType<InputAttr>,
            default: () => {},
        },
        // el-form-item的附加属性
        attr: {
            type: Object as PropType<FormItemAttr>,
            default: () => {},
        },
        // 额外数据,radio、checkbox的选项等数据
        data: {
            type: Object as PropType<InputData>,
            default: () => {},
        },
        prop: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: '',
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const onValueUpdate = (value: modelValueTypes) => {
            emit('update:modelValue', value)
        }

        const blockHelp = computed(() => {
            return props.attr && props.attr['blockHelp'] ? props.attr['blockHelp'] : ''
        })

        // el-form-item 的默认插槽,生成一个baInput
        const defaultSlot = () => {
            let inputNode = createVNode(BaInput, {
                type: props.type,
                attr: { placeholder: props.placeholder, ...props.inputAttr },
                data: props.data,
                modelValue: props.modelValue,
                'onUpdate:modelValue': onValueUpdate,
            })

            if (blockHelp.value) {
                return [
                    inputNode,
                    createVNode(
                        'div',
                        {
                            class: 'block-help',
                        },
                        blockHelp.value
                    ),
                ]
            }
            return inputNode
        }

        // 不带独立label输入框
        const noNeedLabelSlot = [
            'string',
            'password',
            'number',
            'textarea',
            'datetime',
            'year',
            'date',
            'time',
            'select',
            'selects',
            'remoteSelect',
            'remoteSelects',
            'city',
            'icon',
            'color',
        ]

        // 需要独立label的输入框
        const needLabelSlot = ['radio', 'checkbox', 'switch', 'array', 'image', 'images', 'file', 'files', 'editor']

        if (noNeedLabelSlot.includes(props.type)) {
            return () =>
                createVNode(
                    resolveComponent('el-form-item'),
                    {
                        prop: props.prop,
                        ...props.attr,
                        label: props.label,
                    },
                    {
                        default: defaultSlot,
                    }
                )
        } else if (needLabelSlot.includes(props.type)) {
            // 带独立label的输入框
            let title = props.data && props.data.title ? props.data.title : props.label
            const labelSlot = () => {
                return [
                    createVNode(
                        'div',
                        {
                            class: 'ba-form-item-label',
                        },
                        [
                            createVNode('div', null, title),
                            createVNode(
                                'div',
                                {
                                    class: 'ba-form-item-label-tip',
                                },
                                props.data && props.data.tip ? props.data.tip : ''
                            ),
                        ]
                    ),
                ]
            }

            return () =>
                createVNode(
                    resolveComponent('el-form-item'),
                    {
                        class: 'ba-input-item-' + props.type,
                        prop: props.prop,
                        ...props.attr,
                        label: props.label,
                    },
                    {
                        label: labelSlot,
                        default: defaultSlot,
                    }
                )
        }
    },
})
</script>

<style scoped lang="scss">
.ba-form-item-label {
    display: inline-block;
    .ba-form-item-label-tip {
        padding-left: 6px;
        font-size: 12px;
        color: var(--el-text-color-secondary);
    }
}
.ba-form-item-not-support {
    line-height: 15px;
}
.ba-input-item-array :deep(.el-form-item__content) {
    display: block;
    padding-bottom: 32px;
}
</style>
