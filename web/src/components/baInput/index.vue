<script lang="ts">
import {
    createVNode,
    resolveComponent,
    defineComponent,
    PropType,
    VNode,
    computed,
    watch,
    ref,
    reactive,
    resolveDirective,
    withDirectives,
} from 'vue'
import { inputTypes, modelValueTypes } from '/@/components/baInput'
import Array from '/@/components/baInput/array.vue'
import RemoteSelect from '/@/components/baInput/remoteSelect.vue'
import IconSelector from '/@/components/icon/selector.vue'
import { getArea, fileUpload } from '/@/api/common'
import Icon from '/@/components/icon/index.vue'
import { getFileNameFromPath } from '/@/utils/common'
import { genFileId, ElButton } from 'element-plus'
import type { UploadInstance, UploadRawFile, UploadFile } from 'element-plus'
import { useI18n } from 'vue-i18n'
import Editor from '/@/components/editor/index.vue'

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

        // string number textarea password
        const sntp = () => {
            return () =>
                createVNode(resolveComponent('el-input'), {
                    type: props.type == 'string' ? 'text' : props.type,
                    ...props.attr,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
                })
        }
        // radio checkbox
        const rc = () => {
            if (!props.data || !props.data.content) {
                console.warn('请传递 ' + props.type + '的 content')
            }
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
        }
        // select selects
        const select = () => {
            let vNode: VNode[] = []
            if (!props.data || !props.data.content) {
                console.warn('请传递 ' + props.type + '的 content')
            }
            for (const key in props.data.content) {
                vNode.push(
                    createVNode(resolveComponent('el-option'), {
                        key: key,
                        label: props.data.content[key],
                        value: key,
                        ...childrenAttr,
                    })
                )
            }
            return () =>
                createVNode(
                    resolveComponent('el-select'),
                    {
                        class: 'w100',
                        multiple: props.type == 'select' ? false : true,
                        ...props.attr,
                        modelValue: props.modelValue,
                        'onUpdate:modelValue': onValueUpdate,
                    },
                    () => vNode
                )
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
                    ...props.attr,
                    modelValue: props.modelValue,
                    'onUpdate:modelValue': onValueUpdate,
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
                    const valueComputed = computed(() => {
                        if (typeof props.modelValue === 'boolean') {
                            return props.modelValue
                        } else {
                            let valueTmp = parseInt(props.modelValue as string)
                            return valueTmp === NaN || valueTmp <= 0 ? false : true
                        }
                    })
                    return () =>
                        createVNode(resolveComponent('el-switch'), {
                            ...props.attr,
                            modelValue: valueComputed.value,
                            'onUpdate:modelValue': onValueUpdate,
                        })
                },
            ],
            ['datetime', datetime],
            ['year', datetime],
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
                            ...props.attr,
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
                            ...props.attr,
                        })
                },
            ],
            [
                'remoteSelect',
                () => {
                    return () =>
                        createVNode(RemoteSelect, {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...props.attr,
                        })
                },
            ],
            [
                'city',
                () => {
                    type Node = { value?: number; label?: string; leaf?: boolean }
                    let maxLevel = props.data && props.data.level ? props.data.level - 1 : 2
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
                                        for (const key in res.data) {
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
                            ...props.attr,
                        })
                },
            ],
            [
                'image',
                () => {
                    const state = reactive({
                        lastFullUrl: '',
                        showPreview: false,
                        previewUrl: '',
                    })
                    const upload = ref<UploadInstance>()
                    let fileList = props.modelValue
                        ? ref([{ name: getFileNameFromPath(props.modelValue as string), url: props.modelValue }])
                        : ref([])
                    watch(
                        () => props.modelValue,
                        (newVal) => {
                            if (newVal != state.lastFullUrl) {
                                fileList.value = newVal ? [{ name: getFileNameFromPath(newVal as string), url: newVal }] : []
                            }
                        }
                    )
                    return () =>
                        createVNode(
                            resolveComponent('el-upload'),
                            {
                                ref: upload,
                                class: 'ba-upload-image',
                                action: '',
                                'list-type': 'picture-card',
                                'file-list': fileList.value,
                                'auto-upload': false,
                                accept: 'image/*',
                                limit: 1,
                                onExceed: (files: UploadRawFile[]) => {
                                    upload.value!.clearFiles()
                                    const file = files[0] as UploadRawFile
                                    file.uid = genFileId()
                                    upload.value!.handleStart(file)
                                },
                                onChange: (file: UploadFile) => {
                                    if (!file || !file.raw) return
                                    let fd = new FormData()
                                    fd.append('file', file.raw!)
                                    fileUpload(fd).then((res) => {
                                        if (res.code == 1) {
                                            state.lastFullUrl = res.data.file.full_url
                                            onValueUpdate(res.data.file.full_url)
                                        }
                                    })
                                },
                                onRemove: () => {
                                    onValueUpdate('')
                                },
                                onPreview: (file: UploadFile) => {
                                    if (!file || !file.url) {
                                        return
                                    }
                                    state.showPreview = true
                                    state.previewUrl = file.url!
                                },
                                ...props.attr,
                            },
                            () => {
                                return [
                                    createVNode(Icon, {
                                        size: '30',
                                        color: '#c0c4cc',
                                        name: 'el-icon-Plus',
                                    }),
                                    createVNode(
                                        resolveComponent('el-dialog'),
                                        {
                                            modelValue: state.showPreview,
                                            'onUpdate:modelValue': (val: boolean) => {
                                                state.showPreview = val
                                            },
                                            width: '70%',
                                            'append-to-body': true,
                                            'custom-class': 'img-preview-dialog',
                                        },
                                        () => {
                                            return createVNode('img', {
                                                'w-full': true,
                                                src: state.previewUrl,
                                            })
                                        }
                                    ),
                                ]
                            }
                        )
                },
            ],
            [
                'images',
                () => {
                    let urlKey = 0
                    interface UploadFileExt extends UploadFile {
                        urlKey?: number
                    }
                    const state = reactive({
                        showPreview: false,
                        previewUrl: '',
                    })
                    const upload = ref<UploadInstance>()
                    const stringToArray = (val: string | string[]) => {
                        if (typeof val === 'string') {
                            return val == '' ? [] : val.split(',')
                        } else {
                            return val as string[]
                        }
                    }

                    let urls: string[] = []
                    let fileList = ref<{ name: string; url: string; urlKey: number; status?: string }[]>([]) // el-upload 的 fileList,只初始化,无需维护
                    let fullUrls: { url?: string; fullUrl: string; urlKey: number }[] = [] // 完整的urls列表
                    let defaultReturnType: string

                    const init = (modelValue: string | string[]) => {
                        urls = stringToArray(modelValue as string) // 默认值
                        fileList.value = []
                        fullUrls = []
                        defaultReturnType = typeof modelValue === 'string' ? 'string' : 'array'

                        for (const key in urls) {
                            urlKey++
                            fileList.value.push({
                                name: getFileNameFromPath(urls[key]),
                                url: urls[key],
                                urlKey: urlKey,
                            })
                            fullUrls.push({
                                url: urls[key],
                                fullUrl: urls[key],
                                urlKey: urlKey,
                            })
                        }
                    }

                    init(props.modelValue as string)

                    watch(
                        () => props.modelValue,
                        (newVal) => {
                            let newValArr = stringToArray(newVal as string)
                            if (newValArr.sort().toString() != (getFullUrls('array') as string[]).sort().toString()) {
                                init(newVal as string)
                            }
                        }
                    )

                    // 获取当前完整图片路径的列表
                    const getFullUrls = (returnType: string = defaultReturnType) => {
                        let urlList = []
                        for (const key in fullUrls) {
                            urlList.push(fullUrls[key].fullUrl)
                        }
                        return returnType === 'string' ? urlList.join(',') : urlList
                    }

                    return () =>
                        createVNode(
                            resolveComponent('el-upload'),
                            {
                                ref: upload,
                                class: 'ba-upload-image',
                                action: '',
                                'list-type': 'picture-card',
                                'file-list': fileList.value,
                                'auto-upload': false,
                                accept: 'image/*',
                                onChange: (file: UploadFile) => {
                                    if (!file || !file.raw) return
                                    let fd = new FormData()
                                    fd.append('file', file.raw!)
                                    fileUpload(fd).then((res) => {
                                        if (res.code == 1) {
                                            urlKey++
                                            fullUrls.push({
                                                fullUrl: res.data.file.full_url,
                                                urlKey: urlKey,
                                            })
                                            for (const key in fileList.value) {
                                                if (fileList.value[key].status == 'ready' && fileList.value[key].name == file.name) {
                                                    fileList.value[key].status = 'success'
                                                }
                                                if (typeof fileList.value[key].urlKey === 'undefined') {
                                                    fileList.value[key].urlKey = urlKey
                                                }
                                            }
                                            onValueUpdate(getFullUrls())
                                        }
                                    })
                                },
                                onRemove: (file: UploadFileExt) => {
                                    let newFullUrls = []
                                    for (const key in fullUrls) {
                                        if (fullUrls[key].urlKey != file.urlKey) {
                                            newFullUrls.push(fullUrls[key])
                                        }
                                    }
                                    fullUrls = newFullUrls
                                    onValueUpdate(getFullUrls())
                                },
                                onPreview: (file: UploadFile) => {
                                    if (!file || !file.url) {
                                        return
                                    }
                                    state.showPreview = true
                                    state.previewUrl = file.url!
                                },
                                ...props.attr,
                            },
                            () => {
                                return [
                                    createVNode(Icon, {
                                        size: '30',
                                        color: '#c0c4cc',
                                        name: 'el-icon-Plus',
                                    }),
                                    createVNode(
                                        resolveComponent('el-dialog'),
                                        {
                                            modelValue: state.showPreview,
                                            'onUpdate:modelValue': (val: boolean) => {
                                                state.showPreview = val
                                            },
                                            'append-to-body': true,
                                            width: '70%',
                                            'custom-class': 'img-preview-dialog',
                                        },
                                        () => {
                                            return createVNode('img', {
                                                'w-full': true,
                                                src: state.previewUrl,
                                            })
                                        }
                                    ),
                                ]
                            }
                        )
                },
            ],
            [
                'file',
                () => {
                    const lastFullUrl = ref('')
                    const upload = ref<UploadInstance>()
                    let fileList = props.modelValue
                        ? ref([{ name: getFileNameFromPath(props.modelValue as string), url: props.modelValue }])
                        : ref([])
                    watch(
                        () => props.modelValue,
                        (newVal) => {
                            if (newVal != lastFullUrl.value) {
                                fileList.value = newVal ? [{ name: getFileNameFromPath(newVal as string), url: newVal }] : []
                            }
                        }
                    )
                    return () =>
                        createVNode(
                            resolveComponent('el-upload'),
                            {
                                ref: upload,
                                class: 'ba-upload-file w100',
                                action: '',
                                'file-list': fileList.value,
                                'auto-upload': false,
                                limit: 1,
                                onExceed: (files: UploadRawFile[]) => {
                                    upload.value!.clearFiles()
                                    const file = files[0] as UploadRawFile
                                    file.uid = genFileId()
                                    upload.value!.handleStart(file)
                                },
                                onChange: (file: UploadFile) => {
                                    if (!file || !file.raw) return
                                    let fd = new FormData()
                                    fd.append('file', file.raw!)
                                    fileUpload(fd).then((res) => {
                                        if (res.code == 1) {
                                            lastFullUrl.value = res.data.file.full_url
                                            fileList.value = [{ name: file.name, url: res.data.file.full_url }]
                                            onValueUpdate(res.data.file.full_url)
                                        }
                                    })
                                },
                                onRemove: () => {
                                    onValueUpdate('')
                                },
                                ...props.attr,
                            },
                            () => {
                                const blur = resolveDirective('blur')
                                return withDirectives(
                                    createVNode(
                                        ElButton,
                                        {
                                            type: 'primary',
                                        },
                                        () => {
                                            const { t } = useI18n()
                                            return [
                                                createVNode(Icon, {
                                                    color: '#ffffff',
                                                    name: 'el-icon-Upload',
                                                }),
                                                createVNode('span', {}, t('Upload')),
                                            ]
                                        }
                                    ),
                                    [[blur!]]
                                )
                            }
                        )
                },
            ],
            [
                'files',
                () => {
                    let urlKey = 0
                    interface UploadFileExt extends UploadFile {
                        urlKey?: number
                    }
                    const upload = ref<UploadInstance>()
                    const stringToArray = (val: string | string[]) => {
                        if (typeof val === 'string') {
                            return val == '' ? [] : val.split(',')
                        } else {
                            return val as string[]
                        }
                    }

                    let urls: string[] = []
                    let fileList = ref<{ name: string; url: string; urlKey: number; status?: string }[]>([]) // el-upload 的 fileList,只初始化,无需维护
                    let fullUrls: { url?: string; fullUrl: string; urlKey: number }[] = [] // 完整的urls列表
                    let defaultReturnType: string

                    const init = (modelValue: string | string[]) => {
                        urls = stringToArray(modelValue as string) // 默认值
                        fileList.value = []
                        fullUrls = []
                        defaultReturnType = typeof modelValue === 'string' ? 'string' : 'array'

                        for (const key in urls) {
                            urlKey++
                            fileList.value.push({
                                name: getFileNameFromPath(urls[key]),
                                url: urls[key],
                                urlKey: urlKey,
                            })
                            fullUrls.push({
                                url: urls[key],
                                fullUrl: urls[key],
                                urlKey: urlKey,
                            })
                        }
                    }

                    init(props.modelValue as string)

                    watch(
                        () => props.modelValue,
                        (newVal) => {
                            let newValArr = stringToArray(newVal as string)
                            if (newValArr.sort().toString() != (getFullUrls('array') as string[]).sort().toString()) {
                                init(newVal as string)
                            }
                        }
                    )

                    // 获取当前完整文件路径的列表
                    const getFullUrls = (returnType: string = defaultReturnType) => {
                        let urlList = []
                        for (const key in fullUrls) {
                            urlList.push(fullUrls[key].fullUrl)
                        }
                        return returnType === 'string' ? urlList.join(',') : urlList
                    }

                    return () =>
                        createVNode(
                            resolveComponent('el-upload'),
                            {
                                ref: upload,
                                class: 'ba-upload-file w100',
                                action: '',
                                'list-type': 'text',
                                'file-list': fileList.value,
                                'auto-upload': false,
                                onChange: (file: UploadFile) => {
                                    if (!file || !file.raw) return
                                    let fd = new FormData()
                                    fd.append('file', file.raw!)
                                    fileUpload(fd).then((res) => {
                                        if (res.code == 1) {
                                            urlKey++
                                            fullUrls.push({
                                                fullUrl: res.data.file.full_url,
                                                urlKey: urlKey,
                                            })
                                            for (const key in fileList.value) {
                                                if (fileList.value[key].status == 'ready' && fileList.value[key].name == file.name) {
                                                    fileList.value[key].status = 'success'
                                                }
                                                if (typeof fileList.value[key].urlKey === 'undefined') {
                                                    fileList.value[key].urlKey = urlKey
                                                }
                                            }
                                            onValueUpdate(getFullUrls())
                                        }
                                    })
                                },
                                onRemove: (file: UploadFileExt) => {
                                    let newFullUrls = []
                                    for (const key in fullUrls) {
                                        if (fullUrls[key].urlKey != file.urlKey) {
                                            newFullUrls.push(fullUrls[key])
                                        }
                                    }
                                    fullUrls = newFullUrls
                                    onValueUpdate(getFullUrls())
                                },
                                ...props.attr,
                            },
                            () => {
                                const blur = resolveDirective('blur')
                                return withDirectives(
                                    createVNode(
                                        ElButton,
                                        {
                                            type: 'primary',
                                        },
                                        () => {
                                            const { t } = useI18n()
                                            return [
                                                createVNode(Icon, {
                                                    color: '#ffffff',
                                                    name: 'el-icon-Upload',
                                                }),
                                                createVNode('span', {}, t('Upload')),
                                            ]
                                        }
                                    ),
                                    [[blur!]]
                                )
                            }
                        )
                },
            ],
            [
                'icon',
                () => {
                    return () =>
                        createVNode(IconSelector, {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...props.attr,
                        })
                },
            ],
            [
                'editor',
                () => {
                    return () =>
                        createVNode(Editor, {
                            modelValue: props.modelValue,
                            'onUpdate:modelValue': onValueUpdate,
                            ...props.attr,
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
