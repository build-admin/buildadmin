<script lang="ts">
import { createVNode, defineComponent, resolveComponent, PropType } from 'vue'
import { inputTypes, modelValuePropsTypes, modelValueTypes } from '/@/components/baInput'
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
            type: modelValuePropsTypes,
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
    },
    setup(props, { emit }) {
        const onValueUpdate = (value: modelValueTypes) => {
            emit('update:modelValue', value)
        }

        // el-form-item 的默认插槽,生成一个baInput
        const defaultSlot = () => {
            return createVNode(BaInput, {
                type: props.type,
                attr: props.inputAttr,
                data: props.data,
                modelValue: props.modelValue,
                'onUpdate:modelValue': onValueUpdate,
            })
        }

        // 文本和数字输入框
        let noNeedLabelSlot = ['string', 'number', 'textarea', 'datetime', 'select', 'selects']
        if (noNeedLabelSlot.includes(props.type)) {
            return () =>
                createVNode(
                    resolveComponent('el-form-item'),
                    {
                        ...props.attr,
                        label: props.label,
                    },
                    {
                        default: defaultSlot,
                    }
                )
        } else if (props.type == 'radio' || props.type == 'checkbox' || props.type == 'switch' || props.type == 'array') {
            // 带label的输入框
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
                        ...props.attr,
                        label: props.label,
                    },
                    {
                        label: labelSlot,
                        default: defaultSlot,
                    }
                )
        } else {
            console.warn('暂不支持' + props.type + '的输入框类型，你可以自行在 formItem 组件内添加逻辑')
        }
    },
})
</script>

<style scoped lang="scss">
.ba-form-item-label {
    display: flex;
    align-items: center;
    height: 100%;
    .ba-form-item-label-tip {
        padding-left: 6px;
        font-size: 12px;
        color: var(--color-secondary);
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
