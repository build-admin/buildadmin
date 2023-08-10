<template>
    <div class="w100">
        <el-upload
            ref="upload"
            class="ba-upload"
            :class="type"
            v-model:file-list="state.fileList"
            :auto-upload="false"
            @change="onElChange"
            @remove="onElRemove"
            @preview="onElPreview"
            @exceed="onElExceed"
            v-bind="state.attr"
            :key="state.key"
        >
            <!-- 插槽支持，不加 if 时 el-upload 样式会错乱 -->
            <template v-if="slots.default" #default><slot name="default"></slot></template>
            <template v-else #default>
                <template v-if="type == 'image' || type == 'images'">
                    <div v-if="!hideSelectFile" @click.stop="state.selectFile.show = true" class="ba-upload-select-image">
                        {{ $t('utils.choice') }}
                    </div>
                    <Icon class="ba-upload-icon" name="el-icon-Plus" size="30" color="#c0c4cc" />
                </template>
                <template v-else>
                    <el-button v-blur type="primary">
                        <Icon name="el-icon-Plus" color="#ffffff" />
                        <span>{{ $t('Upload') }}</span>
                    </el-button>
                    <el-button v-blur v-if="!hideSelectFile" @click.stop="state.selectFile.show = true" type="success">
                        <Icon name="fa fa-th-list" size="14px" color="#ffffff" />
                        <span class="ml-6">{{ $t('utils.choice') }}</span>
                    </el-button>
                </template>
            </template>
            <template v-if="slots.trigger" #trigger><slot name="trigger"></slot></template>
            <template v-if="slots.tip" #tip><slot name="tip"></slot></template>
            <template v-if="slots.file" #file><slot name="file"></slot></template>
        </el-upload>
        <el-dialog v-model="state.preview.show" class="ba-upload-preview">
            <div class="ba-upload-preview-scroll ba-scroll-style">
                <img :src="state.preview.url" class="ba-upload-preview-img" alt="" />
            </div>
        </el-dialog>
        <SelectFile v-model="state.selectFile.show" v-bind="state.selectFile" @choice="onChoice" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, useSlots, nextTick } from 'vue'
import { UploadInstance, UploadUserFile, UploadProps, genFileId, UploadRawFile, UploadFiles } from 'element-plus'
import { stringToArray } from '/@/components/baInput/helper'
import { fullUrl, arrayFullUrl, getFileNameFromPath, getArrayKey } from '/@/utils/common'
import { fileUpload } from '/@/api/common'
import SelectFile from '/@/components/baInput/components/selectFile.vue'
import { uuid } from '/@/utils/random'
import { cloneDeep, isEmpty } from 'lodash-es'
import { AxiosProgressEvent } from 'axios'
import Sortable from 'sortablejs'

type Writeable<T> = { -readonly [P in keyof T]: T[P] }
interface Props {
    type: 'image' | 'images' | 'file' | 'files'
    // 上传请求时的额外携带数据
    data?: anyObj
    modelValue: string | string[]
    // 返回绝对路径
    returnFullUrl?: boolean
    // 隐藏附件选择器
    hideSelectFile?: boolean
    // 可自定义el-upload的其他属性
    attr?: Partial<Writeable<UploadProps>>
    // 强制上传到本地存储
    forceLocal?: boolean
}
interface UploadFileExt extends UploadUserFile {
    serverUrl?: string
}
interface UploadProgressEvent extends AxiosProgressEvent {
    percent: number
}

const props = withDefaults(defineProps<Props>(), {
    type: 'image',
    data: () => {
        return {}
    },
    modelValue: () => [],
    returnFullUrl: false,
    hideSelectFile: false,
    attr: () => {
        return {}
    },
    forceLocal: false,
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | string[]): void
}>()

const slots = useSlots()
const upload = ref<UploadInstance>()
const state: {
    key: string
    // 返回值类型，通过v-model类型动态计算
    defaultReturnType: 'string' | 'array'
    // 预览弹窗
    preview: {
        show: boolean
        url: string
    }
    // 文件列表
    fileList: UploadFileExt[]
    // el-upload的属性对象
    attr: Partial<UploadProps>
    // 正在上传的文件数量
    uploading: number
    // 显示选择文件窗口
    selectFile: {
        show: boolean
        type?: 'image' | 'file'
        limit?: number
        returnFullUrl: boolean
    }
    events: anyObj
} = reactive({
    key: uuid(),
    defaultReturnType: 'string',
    preview: {
        show: false,
        url: '',
    },
    fileList: [],
    attr: {},
    uploading: 0,
    selectFile: {
        show: false,
        type: 'file',
        returnFullUrl: props.returnFullUrl,
    },
    events: [],
})

const onElChange = (file: UploadFileExt, files: UploadFiles) => {
    // 将 file 换为 files 中的对象，以便修改属性等操作
    const fileIndex = getArrayKey(files, 'uid', file.uid!)
    if (!fileIndex) return
    file = files[fileIndex] as UploadFileExt
    if (!file || !file.raw) return
    if (typeof state.events['beforeUpload'] == 'function' && state.events['beforeUpload'](file) === false) return
    let fd = new FormData()
    fd.append('file', file.raw)
    fd = formDataAppend(fd)
    state.uploading++
    fileUpload(fd, { uuid: uuid() }, props.forceLocal, {
        onUploadProgress: (evt: AxiosProgressEvent) => {
            const progressEvt = evt as UploadProgressEvent
            if (evt.total && evt.total > 0) {
                progressEvt.percent = (evt.loaded / evt.total) * 100
                file.status = 'uploading'
                file.percentage = Math.round(progressEvt.percent)
                typeof state.events['onProgress'] == 'function' && state.events['onProgress'](progressEvt, file, files)
            }
        },
    })
        .then((res) => {
            if (res.code == 1) {
                file.serverUrl = res.data.file.url
                file.status = 'success'
                emits('update:modelValue', getAllUrls())
                typeof state.events['onSuccess'] == 'function' && state.events['onSuccess'](res, file, files)
            } else {
                file.status = 'fail'
                files.splice(fileIndex, 1)
                typeof state.events['onError'] == 'function' && state.events['onError'](res, file, files)
            }
        })
        .catch((res) => {
            file.status = 'fail'
            files.splice(fileIndex, 1)
            typeof state.events['onError'] == 'function' && state.events['onError'](res, file, files)
        })
        .finally(() => {
            state.uploading--
            typeof state.events['onChange'] == 'function' && state.events['onChange'](file, files)
        })
}

const onElRemove = (file: UploadUserFile, files: UploadFiles) => {
    typeof state.events['onRemove'] == 'function' && state.events['onRemove'](file, files)
    emits('update:modelValue', getAllUrls())
}

const onElPreview = (file: UploadFileExt) => {
    typeof state.events['onPreview'] == 'function' && state.events['onPreview'](file)
    if (!file || !file.url) {
        return
    }
    if (props.type == 'file' || props.type == 'files') {
        window.open(fullUrl(file.url))
        return
    }
    state.preview.show = true
    state.preview.url = file.url
}

const onElExceed = (files: UploadUserFile[]) => {
    const file = files[0] as UploadRawFile
    file.uid = genFileId()
    upload.value!.handleStart(file)
    typeof state.events['onExceed'] == 'function' && state.events['onExceed'](file, files)
}

const onChoice = (files: string[]) => {
    let oldValArr = getAllUrls('array') as string[]
    files = oldValArr.concat(files)
    init(files)
    emits('update:modelValue', getAllUrls())
    typeof state.events['onChange'] == 'function' && state.events['onChange'](files, state.fileList)
    state.selectFile.show = false
}

onMounted(() => {
    if (props.type == 'image' || props.type == 'file') {
        state.attr = { ...state.attr, limit: 1 }
    } else {
        state.attr = { ...state.attr, multiple: true }
    }

    if (props.type == 'image' || props.type == 'images') {
        state.selectFile.type = 'image'
        state.attr = { ...state.attr, accept: 'image/*', listType: 'picture-card' }
    }

    const addProps: anyObj = {}
    const evtArr = ['onPreview', 'onRemove', 'onSuccess', 'onError', 'onChange', 'onExceed', 'beforeUpload', 'onProgress']
    for (const key in props.attr) {
        if (evtArr.includes(key)) {
            state.events[key] = props.attr[key as keyof typeof props.attr]
        } else {
            addProps[key] = props.attr[key as keyof typeof props.attr]
        }
    }

    state.attr = { ...state.attr, ...addProps }
    if (state.attr.limit) state.selectFile.limit = state.attr.limit

    init(props.modelValue)

    nextTick(() => {
        let uploadListEl = upload.value?.$el.querySelector('.el-upload-list')
        let uploadItemEl = uploadListEl.getElementsByClassName('el-upload-list__item')
        if (uploadItemEl.length >= 2) {
            Sortable.create(uploadListEl, {
                animation: 200,
                draggable: '.el-upload-list__item',
                onEnd: (evt: Sortable.SortableEvent) => {
                    if (evt.oldIndex != evt.newIndex) {
                        state.fileList[evt.newIndex!] = [
                            state.fileList[evt.oldIndex!],
                            (state.fileList[evt.oldIndex!] = state.fileList[evt.newIndex!]),
                        ][0]
                        emits('update:modelValue', getAllUrls())
                    }
                },
            })
        }
    })
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (state.uploading > 0) return
        if (newVal === undefined || newVal === null) {
            return init('')
        }
        let newValArr = arrayFullUrl(stringToArray(cloneDeep(newVal)))
        let oldValArr = arrayFullUrl(getAllUrls('array'))
        if (newValArr.sort().toString() != oldValArr.sort().toString()) {
            init(newVal)
        }
    }
)

const limitExceed = () => {
    if (state.attr.limit && state.fileList.length > state.attr.limit) {
        state.fileList = state.fileList.slice(state.fileList.length - state.attr.limit)
        return true
    }
    return false
}

const init = (modelValue: string | string[]) => {
    let urls = stringToArray(modelValue as string)
    state.fileList = []
    state.defaultReturnType = typeof modelValue === 'string' || props.type == 'file' || props.type == 'image' ? 'string' : 'array'

    for (const key in urls) {
        state.fileList.push({
            name: getFileNameFromPath(urls[key]),
            url: fullUrl(urls[key]),
            serverUrl: urls[key],
        })
    }

    // 超出过滤 || 确定返回的URL完整
    if (limitExceed() || props.returnFullUrl) {
        emits('update:modelValue', getAllUrls())
    }
    state.key = uuid()
}

// 获取当前所有图片路径的列表
const getAllUrls = (returnType: string = state.defaultReturnType) => {
    limitExceed()
    let urlList = []
    for (const key in state.fileList) {
        if (state.fileList[key].serverUrl) urlList.push(state.fileList[key].serverUrl)
    }
    if (props.returnFullUrl) urlList = arrayFullUrl(urlList as string[])
    return returnType === 'string' ? urlList.join(',') : (urlList as string[])
}

const formDataAppend = (fd: FormData) => {
    if (props.data && !isEmpty(props.data)) {
        for (const key in props.data) {
            fd.append(key, props.data[key])
        }
    }
    return fd
}

const getUploadRef = () => {
    return upload.value
}

const showSelectFile = () => {
    state.selectFile.show = true
}

defineExpose({
    getUploadRef,
    showSelectFile,
})
</script>

<style scoped lang="scss">
.ba-upload-select-image {
    position: absolute;
    top: 0px;
    border: 1px dashed var(--el-border-color);
    border-top: 1px dashed transparent;
    width: var(--el-upload-picture-card-size);
    height: 30px;
    line-height: 30px;
    border-radius: 6px;
    border-bottom-right-radius: 20px;
    border-bottom-left-radius: 20px;
    text-align: center;
    font-size: var(--el-font-size-extra-small);
    color: var(--el-text-color-regular);
    &:hover {
        color: var(--el-color-primary);
        border: 1px dashed var(--el-color-primary);
        border-top: 1px dashed var(--el-color-primary);
    }
}
.ba-upload :deep(.el-upload:hover .ba-upload-icon) {
    color: var(--el-color-primary) !important;
}
:deep(.ba-upload-preview) .el-dialog__body {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    height: auto;
}
.ba-upload-preview-scroll {
    max-height: 70vh;
    overflow: auto;
}
.ba-upload-preview-img {
    max-width: 100%;
    max-height: 100%;
}
:deep(.el-dialog__headerbtn) {
    top: 2px;
    width: 37px;
    height: 37px;
}
.ba-upload.image :deep(.el-upload--picture-card),
.ba-upload.images :deep(.el-upload--picture-card) {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ba-upload.file :deep(.el-upload-list),
.ba-upload.files :deep(.el-upload-list) {
    margin-left: -10px;
}
.ba-upload.files,
.ba-upload.images {
    :deep(.el-upload-list__item) {
        .el-upload-list__item-actions,
        .el-upload-list__item-name {
            cursor: move;
        }
    }
}
.ml-6 {
    margin-left: 6px;
}
</style>
