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
        >
            <!-- 插槽支持，不加 if 时 el-upload 样式会错乱 -->
            <template v-if="slots.default" #default><slot name="default"></slot></template>
            <template v-else #default>
                <Icon v-if="type == 'image' || type == 'images'" name="el-icon-Plus" size="30" color="#c0c4cc" />
                <el-button v-else type="primary">
                    <Icon name="el-icon-Plus" color="#ffffff" />
                    <span>{{ $t('Upload') }}</span>
                </el-button>
            </template>
            <template v-if="slots.trigger" #trigger><slot name="trigger"></slot></template>
            <template v-if="slots.tip" #tip><slot name="tip"></slot></template>
            <template v-if="slots.file" #file><slot name="file"></slot></template>
        </el-upload>
        <el-dialog v-model="state.preview.show" custom-class="ba-upload-preview">
            <img :src="state.preview.url" class="ba-upload-preview-img" alt="" />
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, useSlots } from 'vue'
import { UploadInstance, UploadUserFile, UploadProps, genFileId, UploadRawFile } from 'element-plus'
import { stringToArray } from '/@/components/baInput/helper'
import { fullUrl, getFileNameFromPath } from '/@/utils/common'
import { fileUpload } from '/@/api/common'
import { uuid } from '/@/utils/random'
import _ from 'lodash'

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
    // 返回值类型，通过v-model类型动态计算
    defaultReturnType: 'string' | 'array'
    // 预览弹窗
    preview: {
        show: boolean
        url: string
    }
    urls: string[]
    // 文件列表
    fileList: UploadFileExt[]
    // el-upload的属性对象
    attr: Partial<UploadProps>
    isExceed: boolean
    uploading: number
} = reactive({
    defaultReturnType: 'string',
    preview: {
        show: false,
        url: '',
    },
    urls: [],
    fileList: [],
    attr: {},
    isExceed: false,
    uploading: 0,
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
                if (state.isExceed) {
                    state.fileList.push(file)
                }
                file.serverUrl = props.returnFullUrl ? res.data.file.full_url : res.data.file.url
                emits('update:modelValue', getAllUrls())
                file.status = 'success'
            }
        })
        .catch(() => {
            file.status = 'fail'
        })
        .finally(() => {
            state.uploading--
            state.isExceed = false
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
    state.isExceed = true
    // 虽然是v-model，但此处任然需要手动维护 state.fileList
    limitExceed()
    upload.value!.clearFiles()
    const file = files[0] as UploadRawFile
    file.uid = genFileId()
    upload.value!.handleStart(file)
}

onMounted(() => {
    if (props.type == 'image' || props.type == 'file') {
        state.attr = { ...state.attr, limit: 1 }
    } else {
        state.attr = { ...state.attr, multiple: true }
    }

    if (props.type == 'image' || props.type == 'images') {
        state.attr = { ...state.attr, accept: 'image/*', listType: 'picture-card' }
    }

    state.attr = { ...state.attr, ...props.attr }

    init(props.modelValue)
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (state.uploading > 0) return
        if (newVal === undefined) {
            return init('')
        }
        let newValArr = stringToArray(_.cloneDeep(newVal))
        if (newValArr.sort().toString() != (getAllUrls('array') as string[]).sort().toString()) {
            init(newVal as string)
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
    state.urls = stringToArray(modelValue as string) // 默认值
    state.fileList = []
    state.defaultReturnType = typeof modelValue === 'string' || props.type == 'file' || props.type == 'image' ? 'string' : 'array'

    for (const key in state.urls) {
        state.fileList.push({
            name: getFileNameFromPath(state.urls[key]),
            url: fullUrl(state.urls[key]),
            serverUrl: props.returnFullUrl ? fullUrl(state.urls[key]) : state.urls[key],
        })
    }

    // 超出过滤 || 确定返回的URL完整
    if (limitExceed() || props.returnFullUrl) {
        emits('update:modelValue', getAllUrls())
    }
}

// 获取当前所有图片路径的列表
const getAllUrls = (returnType: string = state.defaultReturnType) => {
    limitExceed()
    let urlList = []
    for (const key in state.fileList) {
        urlList.push(state.fileList[key].serverUrl)
    }
    return returnType === 'string' ? (urlList.join(',') as string) : (urlList as string[])
}

const formDataAppend = (fd: FormData) => {
    if (props.data && !_.isEmpty(props.data)) {
        for (const key in props.data) {
            fd.append(key, props.data[key])
        }
    }
    return fd
}
</script>

<style scoped lang="scss">
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ba-upload.file :deep(.el-upload-list),
.ba-upload.files :deep(.el-upload-list) {
    margin-left: -10px;
}
</style>
