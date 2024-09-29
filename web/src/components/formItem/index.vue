<script lang="ts">
import { formItemProps } from 'element-plus'
import type { PropType, VNode } from 'vue'
import { computed, createVNode, defineComponent, resolveComponent } from 'vue'
import type { InputAttr, InputData, ModelValueTypes } from '/@/components/baInput'
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
        blockHelp: {
            type: String,
            default: '',
        },
        tip: [String, Object],
        // el-form-item 的附加属性（还可以直接通过当前组件的 props 传递）
        attr: {
            type: Object as PropType<FormItemAttr>,
            default: () => {},
        },
        // 额外数据（已和 props.inputAttr 合并，还可以通过它进行传递）
        data: {
            type: Object as PropType<InputData>,
            default: () => {},
        },
        // 内部输入框的 placeholder（相当于 props.inputAttr.placeholder 的别名）
        placeholder: {
            type: String,
            default: '',
        },
        ...formItemProps,
    },
    emits: ['update:modelValue'],
    setup(props, { emit, slots }) {
        // 通过 props 和 props.attr 两种方式传递的属性汇总为 attrs
        const excludeProps = ['type', 'modelValue', 'inputAttr', 'attr', 'data', 'placeholder']
        const attrs = computed(() => {
            const newAttrs = props.attr || {}
            for (const key in props) {
                const propValue: any = props[key as keyof typeof props]
                if (!excludeProps.includes(key) && (propValue || propValue === false)) {
                    newAttrs[key as keyof typeof props.attr] = propValue
                }
            }
            return newAttrs
        })

        const onValueUpdate = (value: ModelValueTypes) => {
            emit('update:modelValue', value)
        }

        // el-form-item 的插槽
        const formItemSlots: { [key: string]: () => VNode | VNode[] } = {}

        // default 插槽
        formItemSlots.default = () => {
            let inputNode = createVNode(
                BaInput,
                {
                    type: props.type,
                    attr: { placeholder: props.placeholder, ...props.inputAttr, ...props.data },
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                },
                slots
            )

            if (attrs.value.blockHelp) {
                return [
                    inputNode,
                    createVNode(
                        'div',
                        {
                            class: 'block-help',
                        },
                        attrs.value.blockHelp
                    ),
                ]
            }
            return inputNode
        }

        if (attrs.value.tip) {
            const createTipNode = () => {
                const tipProps = typeof attrs.value.tip === 'string' ? { content: attrs.value.tip, placement: 'top' } : attrs.value.tip
                return createVNode(resolveComponent('el-tooltip'), tipProps, {
                    default: () => [
                        createVNode('i', {
                            class: 'fa fal fa-question-circle',
                        }),
                    ],
                })
            }

            // label 插槽
            formItemSlots.label = () => {
                return createVNode(
                    'span',
                    {
                        class: 'ba-form-item-label',
                    },
                    [
                        createVNode('span', null, attrs.value.label),
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
                    ...attrs.value,
                },
                formItemSlots
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
