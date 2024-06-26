<script lang="ts">
import type { PropType, VNode } from 'vue'
import { computed, createVNode, defineComponent, resolveComponent } from 'vue'
import type { InputAttr, InputData, modelValueTypes } from '/@/components/baInput'
import { inputTypes } from '/@/components/baInput'
import BaInput from '/@/components/baInput/index.vue'
import type { FormItemAttr } from '/@/components/formItem'

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

        // el-form-item 插槽
        const slots: { [key: string]: () => VNode | VNode[] } = {}

        // default 插槽
        slots.default = () => {
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

        if (props.data && props.data.tip) {
            const createTipNode = () => {
                const tipProps = typeof props.data.tip === 'string' ? { content: props.data.tip } : props.data.tip
                return createVNode(resolveComponent('el-tooltip'), tipProps, {
                    default: () => [
                        createVNode('i', {
                            class: 'fa fal fa-question-circle',
                        }),
                    ],
                })
            }

            // label 插槽
            slots.label = () => {
                return createVNode(
                    'span',
                    {
                        class: 'ba-form-item-label',
                    },
                    [
                        createVNode('span', null, props.label),
                        createVNode(
                            'span',
                            {
                                class: 'ba-form-item-label-tip',
                            },
                            [createTipNode()]
                        ),
                    ]
                )
            }
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
                    ...slots,
                }
            )
    },
})
</script>

<style scoped lang="scss">
.ba-form-item-label-tip {
    padding-left: 6px;
    color: var(--el-text-color-secondary);
    i {
        cursor: pointer;
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
