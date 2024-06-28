<script lang="ts">
import { isArray } from 'lodash-es'
import type { PropType, VNode } from 'vue'
import { computed, createVNode, defineComponent, reactive, resolveComponent } from 'vue'
import { getArea } from '/@/api/common'
import type { InputAttr, InputData, modelValueTypes } from '/@/components/baInput'
import { inputTypes } from '/@/components/baInput'
import Array from '/@/components/baInput/components/array.vue'
import BaUpload from '/@/components/baInput/components/baUpload.vue'
import Editor from '/@/components/baInput/components/editor.vue'
import IconSelector from '/@/components/baInput/components/iconSelector.vue'
import RemoteSelect from '/@/components/baInput/components/remoteSelect.vue'

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
            type: null,
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
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        // 合并 props.attr 和 props.data
        const attrs = { ...props.attr, ...props.data }

        // 通用值更新函数
        const onValueUpdate = (value: modelValueTypes) => {
            emit('update:modelValue', value)
        }

        // 子级元素属性
        const childrenAttr = attrs.childrenAttr ? attrs.childrenAttr : {}

        // string number textarea password
        const sntp = () => {
            return () =>
                createVNode(resolveComponent('el-input'), {
                    type: props.type == 'string' ? 'text' : props.type,
                    ...attrs,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        }
        // radio checkbox
        const rc = () => {
            if (!attrs.content) {
                console.warn('请传递 ' + props.type + ' 的 content')
            }
            let vNode: VNode[] = []
            const contentIsArray = isArray(attrs.content)
            const type = attrs.button ? props.type + '-button' : props.type
            for (const key in attrs.content) {
                let nodeProps = {}
                if (contentIsArray) {
                    if (typeof attrs.content[key].value == 'number') {
                        console.warn(props.type + ' 的 content.value 不能是数字')
                    }

                    nodeProps = {
                        ...attrs.content[key],
                        border: attrs.border ? attrs.border : false,
                        ...childrenAttr,
                    }
                } else {
                    nodeProps = {
                        value: key,
                        label: attrs.content[key],
                        border: attrs.border ? attrs.border : false,
                        ...childrenAttr,
                    }
                }
                vNode.push(createVNode(resolveComponent('el-' + type), nodeProps))
            }
            return () => {
                const valueComputed = computed(() => {
                    if (props.type == 'radio') {
                        if (props.modelValue == undefined) return ''
                        return '' + props.modelValue
                    } else {
                        let modelValueArr: anyObj = []
                        for (const key in props.modelValue) {
                            modelValueArr[key] = '' + props.modelValue[key]
                        }
                        return modelValueArr
                    }
                })
                return createVNode(
                    resolveComponent('el-' + props.type + '-group'),
                    {
                        ...attrs,
                        modelValue: valueComputed.value,
                        'onUpdate:modelValue': onValueUpdate,
                    },
                    () => vNode
                )
            }
        }
        // select selects
        const select = () => {
            let vNode: VNode[] = []
            if (!attrs.content) {
                console.warn('请传递 ' + props.type + '的 content')
            }
            for (const key in attrs.content) {
                vNode.push(
                    createVNode(resolveComponent('el-option'), {
                        key: key,
                        label: attrs.content[key],
                        value: key,
                        ...childrenAttr,
                    })
                )
            }
            return () => {
                const valueComputed = computed(() => {
                    if (props.type == 'select') {
                        if (props.modelValue == undefined) return ''
                        return '' + props.modelValue
                    } else {
                        let modelValueArr: anyObj = []
                        for (const key in props.modelValue) {
                            modelValueArr[key] = '' + props.modelValue[key]
                        }
                        return modelValueArr
                    }
                })
                return createVNode(
                    resolveComponent('el-select'),
                    {
                        class: 'w100',
                        multiple: props.type == 'select' ? false : true,
                        clearable: true,
                        ...attrs,
                        modelValue: valueComputed.value,
                        'onUpdate:modelValue': onValueUpdate,
                    },
                    () => vNode
                )
            }
        }
        // datetime
        const datetime = () => {
            let valueFormat = 'YYYY-MM-DD HH:mm:ss'
            switch (props.type) {
                case 'date':
                    valueFormat = 'YYYY-MM-DD'
                    break
                case 'year':
                    valueFormat = 'YYYY'
                    break
            }
            return () =>
                createVNode(resolveComponent('el-date-picker'), {
                    class: 'w100',
                    type: props.type,
                    'value-format': valueFormat,
                    ...attrs,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        }
        // upload
        const upload = () => {
            return () =>
                createVNode(BaUpload, {
                    type: props.type,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                    ...attrs,
                })
        }

        // remoteSelect remoteSelects
        const remoteSelect = () => {
            return () =>
                createVNode(RemoteSelect, {
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                    multiple: props.type == 'remoteSelect' ? false : true,
                    ...attrs,
                })
        }

        const buildFun = new Map([
            ['string', sntp],
            ['number', sntp],
            ['textarea', sntp],
            ['password', sntp],
            ['radio', rc],
            ['checkbox', rc],
            [
                'switch',
                () => {
                    // 值类型:string,number,boolean,custom
                    const valueType = computed(() => {
                        if (typeof attrs.activeValue !== 'undefined' && typeof attrs.inactiveValue !== 'undefined') {
                            return 'custom'
                        }
                        return typeof props.modelValue
                    })

                    // 要传递给 el-switch 组件的绑定值，该组件对传入值有限制，先做处理
                    const valueComputed = computed(() => {
                        if (valueType.value === 'boolean' || valueType.value === 'custom') {
                            return props.modelValue
                        } else {
                            let valueTmp = parseInt(props.modelValue as string)
                            return isNaN(valueTmp) || valueTmp <= 0 ? false : true
                        }
                    })
                    return () =>
                        createVNode(resolveComponent('el-switch'), {
                            ...attrs,
                            modelValue: valueComputed.value,
                            'onUpdate:modelValue': (value: boolean) => {
                                let newValue: boolean | string | number = value
                                switch (valueType.value) {
                                    case 'string':
                                        newValue = value ? '1' : '0'
                                        break
                                    case 'number':
                                        newValue = value ? 1 : 0
                                }
                                emit('update:modelValue', newValue)
                            },
                        })
                },
            ],
            ['datetime', datetime],
            [
                'year',
                () => {
                    return () => {
                        const valueComputed = computed(() => (!props.modelValue ? null : '' + props.modelValue))
                        return createVNode(resolveComponent('el-date-picker'), {
                            class: 'w100',
                            type: props.type,
                            'value-format': 'YYYY',
                            ...attrs,
                            modelValue: valueComputed.value,
                            'onUpdate:modelValue': onValueUpdate,
                        })
                    }
                },
            ],
            ['date', datetime],
            [
                'time',
                () => {
                    const valueComputed = computed(() => {
                        if (props.modelValue instanceof Date) {
                            return props.modelValue
                        } else if (!props.modelValue) {
                            return ''
                        } else {
                            let date = new Date()
                            return new Date(date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + props.modelValue)
                        }
                    })
                    return () =>
                        createVNode(resolveComponent('el-time-picker'), {
                            class: 'w100',
                            clearable: true,
                            format: 'HH:mm:ss',
                            ...attrs,
                            modelValue: valueComputed.value,
                            'onUpdate:modelValue': onValueUpdate,
                        })
                },
            ],
            ['select', select],
            ['selects', select],
            [
                'array',
                () => {
                    return () =>
                        createVNode(Array, {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...attrs,
                        })
                },
            ],
            ['remoteSelect', remoteSelect],
            ['remoteSelects', remoteSelect],
            [
                'city',
                () => {
                    type Node = { value?: number; label?: string; leaf?: boolean }
                    let maxLevel = attrs.level ? attrs.level - 1 : 2
                    const lastLazyValue: {
                        value: string | number[] | unknown
                        nodes: Node[]
                        key: string
                        currentRequest: any
                    } = reactive({
                        value: 'ready',
                        nodes: [],
                        key: '',
                        currentRequest: null,
                    })

                    // 请求到的node备份-s
                    let nodeEbak: anyObj = {}
                    const getNodes = (level: number, key: string) => {
                        if (nodeEbak[level] && nodeEbak[level][key]) {
                            return nodeEbak[level][key]
                        }
                        return false
                    }
                    const setNodes = (level: number, key: string, nodes: Node[] = []) => {
                        if (!nodeEbak[level]) {
                            nodeEbak[level] = {}
                        }
                        nodeEbak[level][key] = nodes
                    }
                    // 请求到的node备份-e

                    return () =>
                        createVNode(resolveComponent('el-cascader'), {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            class: 'w100',
                            clearable: true,
                            props: {
                                lazy: true,
                                lazyLoad(node: any, resolve: any) {
                                    // lazyLoad会频繁触发,在本地存储请求结果,供重复触发时直接读取
                                    const { level, pathValues } = node
                                    let key = pathValues.join(',')
                                    key = key ? key : 'init'

                                    let locaNode = getNodes(level, key)
                                    if (locaNode) {
                                        return resolve(locaNode)
                                    }

                                    if (lastLazyValue.key == key && lastLazyValue.value == props.modelValue) {
                                        if (lastLazyValue.currentRequest) {
                                            return lastLazyValue.currentRequest
                                        }
                                        return resolve(lastLazyValue.nodes)
                                    }

                                    let nodes: Node[] = []
                                    lastLazyValue.key = key
                                    lastLazyValue.value = props.modelValue
                                    lastLazyValue.currentRequest = getArea(pathValues).then((res) => {
                                        let toStr = false
                                        if (props.modelValue && typeof (props.modelValue as anyObj)[0] === 'string') {
                                            toStr = true
                                        }
                                        for (const key in res.data) {
                                            if (toStr) {
                                                res.data[key].value = res.data[key].value.toString()
                                            }
                                            res.data[key].leaf = level >= maxLevel
                                            nodes.push(res.data[key])
                                        }
                                        lastLazyValue.nodes = nodes
                                        lastLazyValue.currentRequest = null
                                        setNodes(level, key, nodes)
                                        resolve(nodes)
                                    })
                                },
                            },
                            ...attrs,
                        })
                },
            ],
            ['image', upload],
            ['images', upload],
            ['file', upload],
            ['files', upload],
            [
                'icon',
                () => {
                    return () =>
                        createVNode(IconSelector, {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...attrs,
                        })
                },
            ],
            [
                'color',
                () => {
                    return () =>
                        createVNode(resolveComponent('el-color-picker'), {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...attrs,
                        })
                },
            ],
            [
                'editor',
                () => {
                    return () =>
                        createVNode(Editor, {
                            class: 'w100',
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...attrs,
                        })
                },
            ],
            [
                'default',
                () => {
                    console.warn('暂不支持' + props.type + '的输入框类型，你可以自行在 BaInput 组件内添加逻辑')
                },
            ],
        ])

        let action = buildFun.get(props.type) || buildFun.get('default')
        return action!.call(this)
    },
})
</script>

<style scoped lang="scss">
.ba-upload-image :deep(.el-upload--picture-card) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ba-upload-file :deep(.el-upload-list) {
    margin-left: -10px;
}
</style>
