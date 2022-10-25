<template>
    <div class="w100">
        <el-upload
            ref="upload"
            class="ba-upload"
            :class="type"
            v-model:file-list="state.fileList"
            :auto-upload="false"
            @change="onChange"
            @remove="onRemove"
            @preview="onPreview"
            @exceed="onExceed"
            v-bind="state.attr"
            :key="state.key"
        >
            <!-- 插槽支持，不加 if 时 el-upload 样式会错乱 -->
            <template v-if="slots.default" #default><slot name="default"></slot></template>
            <template v-else #default>
                <template v-if="type == 'image' || type == 'images'">
                    <div @click.stop="state.selectFile.show = true" class="ba-upload-select-image">{{ $t('routine.attachment.choice') }}</div>
                    <Icon class="ba-upload-icon" name="el-icon-Plus" size="30" color="#c0c4cc" />
                </template>
                <template v-else>
                    <el-button type="primary">
                        <Icon name="el-icon-Plus" color="#ffffff" />
                        <span>{{ $t('Upload') }}</span>
                    </el-button>
                    <el-button @click.stop="state.selectFile.show = true" type="success">
                        <Icon name="fa fa-th-list" size="14px" color="#ffffff" />
                        <span class="ml-6">{{ $t('routine.attachment.choice') }}</span>
                    </el-button>
                </template>
            </template>
            <template v-if="slots.trigger" #trigger><slot name="trigger"></slot></template>
            <template v-if="slots.tip" #tip><slot name="tip"></slot></template>
            <template v-if="slots.file" #file><slot name="file"></slot></template>
        </el-upload>
        <el-dialog v-model="state.preview.show" class="ba-upload-preview">
            <img :src="state.preview.url" class="ba-upload-preview-img" alt="" />
        </el-dialog>
        <SelectFile v-model="state.selectFile.show" v-bind="state.selectFile" @choice="onChoice" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, useSlots } from 'vue'
import { UploadInstance, UploadUserFile, UploadProps, genFileId, UploadRawFile } from 'element-plus'
import { stringToArray } from '/@/components/baInput/helper'
import { fullUrl, arrayFullUrl, getFileNameFromPath } from '/@/utils/common'
import { fileUpload } from '/@/api/common'
import SelectFile from '/@/components/baInput/components/selectFile.vue'
import { uuid } from '/@/utils/random'
import { cloneDeep, isEmpty } from 'lodash-es'

interface Props {
    type: 'image' | 'images' | 'file' | 'files'
    // 上传请求时的额外携带数据
    data?: anyObj
    modelValue: string | string[]
    // 返回绝对路径
    returnFullUrl?: boolean
    // 可自定义el-upload的其他属性
    attr?: Partial<UploadProps>
}
interface UploadFileExt extends UploadUserFile {
    serverUrl?: string
}

const props = withDefaults(defineProps<Props>(), {
    type: 'image',
    data: () => {
        return {}
    },
    modelValue: () => [],
    returnFullUrl: false,
    attr: () => {
        return {}
    },
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
})

const onChange = (file: UploadFileExt) => {
    if (!file || !file.raw) return
    let fd = new FormData()
    fd.append('file', file.raw!)
    fd = formDataAppend(fd)
    state.uploading++
    fileUpload(fd, { uuid: uuid() })
        .then((res) => {
            if (res.code == 1) {
                file.serverUrl = res.data.file.url
                file.status = 'success'
                emits('update:modelValue', getAllUrls())
            }
        })
        .catch(() => {
            file.status = 'fail'
        })
        .finally(() => {
            state.uploading--
        })
}

const onRemove = () => {
    emits('update:modelValue', getAllUrls())
}

const onPreview = (file: UploadFileExt) => {
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

const onExceed = (files: UploadUserFile[]) => {
    const file = files[0] as UploadRawFile
    file.uid = genFileId()
    upload.value!.handleStart(file)
}

const onChoice = (files: string[]) => {
    let oldValArr = getAllUrls('array') as string[]
    files = oldValArr.concat(files)
    init(files)
    emits('update:modelValue', getAllUrls())
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

    state.attr = { ...state.attr, ...props.attr }
    if (state.attr.limit) state.selectFile.limit = state.attr.limit

    init(props.modelValue)
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (state.uploading > 0) return
        if (newVal === undefined) {
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
}
.ba-upload-preview-img {
    max-width: 100%;
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
.ml-6 {
    margin-left: 6px;
}
</style>
