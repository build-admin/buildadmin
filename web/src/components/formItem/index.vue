<script lang="ts">
import { formItemProps } from 'element-plus'
import type { PropType, VNode } from 'vue'
import { createVNode, defineComponent, resolveComponent } from 'vue'
import type { InputAttr, InputData, modelValueTypes } from '/@/components/baInput'
import { inputTypes } from '/@/components/baInput'
import BaInput from '/@/components/baInput/index.vue'
import type { FormItemAttr } from '/@/components/formItem'

export default defineComponent({
    name: 'formItem',
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
            required: true,
        },
        // 输入框的附加属性
        inputAttr: {
            type: Object as PropType<InputAttr>,
            default: () => {},
        },
        // el-form-item 的附加属性(还可以直接通过当前组件的 props 传递)
        attr: {
            type: Object as PropType<FormItemAttr>,
            default: () => {},
        },
        // 额外数据
        data: {
            type: Object as PropType<InputData>,
            default: () => {},
        },
        placeholder: {
            type: String,
            default: '',
        },
        blockHelp: {
            type: String,
            default: '',
        },
        ...formItemProps,
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        // 通过 props 和 props.attr 两种方式传递的属性在此汇总
        const attrs = props.attr || {}
        for (const key in props) {
            if (!['type', 'modelValue', 'inputAttr', 'attr', 'data', 'placeholder'].includes(key)) {
                attrs[key as keyof typeof props.attr] = (props[key as keyof typeof props] as any) || attrs[key as keyof typeof props.attr]
            }
        }

        const onValueUpdate = (value: modelValueTypes) => {
            emit('update:modelValue', value)
        }

        // el-form-item 的插槽
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

            if (attrs.blockHelp) {
                return [
                    inputNode,
                    createVNode(
                        'div',
                        {
                            class: 'block-help',
                        },
                        attrs.blockHelp
                    ),
                ]
            }
            return inputNode
        }

        if (attrs.tip) {
            const createTipNode = () => {
                const tipProps = typeof attrs.tip === 'string' ? { content: attrs.tip } : attrs.tip
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
                        createVNode('span', null, attrs.label),
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
                    ...attrs,
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
