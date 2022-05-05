<template>
    <div v-if="state.mounted" :style="style" class="ba-editor wangeditor">
        <Toolbar class="wangeditor-toolbar" :editor="editorRef" :defaultConfig="toolbarConfig" :mode="mode" />
        <Editor
            :style="state.editorStyle"
            v-model="state.value"
            :defaultConfig="state.editorConfig"
            :mode="mode"
            @onCreated="handleCreated"
            @onChange="handleChange"
            v-bind="$attrs"
        />
    </div>
</template>

<script setup lang="ts">
import '@wangeditor/editor/dist/css/style.css' // 引入 css
import { onBeforeUnmount, reactive, shallowRef, onMounted, CSSProperties, computed, watch } from 'vue'
import { IEditorConfig, IToolbarConfig, i18nChangeLanguage } from '@wangeditor/editor'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import { useConfig } from '/@/stores/config'

interface Props {
    // 编辑区高度
    height?: string
    mode?: 'default' | 'simple'
    placeholder?: string
    modelValue: string
    // https://www.wangeditor.com/v5/toolbar-config.html#getconfig
    toolbarConfig?: Partial<IToolbarConfig>
    // https://www.wangeditor.com/v5/editor-config.html#placeholder
    editorConfig?: Partial<IEditorConfig>
    // 编辑区style
    editorStyle?: CSSProperties
    // 整体的style
    style?: CSSProperties
}

const props = withDefaults(defineProps<Props>(), {
    height: '320px',
    mode: 'default',
    placeholder: '请输入内容...',
    modelValue: '',
    toolbarConfig: () => {
        return {}
    },
    editorConfig: () => {
        return {}
    },
    editorStyle: () => {
        {
            return {}
        }
    },
    style: () => {
        return {}
    },
})

const config = useConfig()
const lang = computed(() => config.lang.defaultLang)
const editorRef = shallowRef()
const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()

const state: {
    mounted: boolean
    value: string
    editorConfig: Partial<IEditorConfig>
    editorStyle: CSSProperties
} = reactive({
    mounted: false,
    value: props.modelValue,
    editorConfig: props.editorConfig,
    editorStyle: props.editorStyle,
})

onMounted(() => {
    i18nChangeLanguage(lang.value == 'zh-cn' ? 'zh-CN' : lang.value)
    state.editorConfig.placeholder = props.placeholder
    state.editorStyle.height = props.height
    state.editorStyle['overflow-y'] = 'hidden'
    state.mounted = true
})

// 组件销毁时，也及时销毁编辑器
onBeforeUnmount(() => {
    if (editorRef.value == null) return
    editorRef.value.destroy()
})

const handleCreated = (editor: any) => {
    editorRef.value = editor // 记录 editor 实例
}

const handleChange = () => {
    emits('update:modelValue', editorRef.value.getHtml())
}

const getRef = () => {
    return editorRef.value
}

defineExpose({
    getRef,
})

watch(() => props.modelValue, (newVal) => {
    state.value = newVal
})
</script>

<style scoped lang="scss">
.ba-editor {
    border: 1px solid var(--color-sub-3);
}
.wangeditor-toolbar {
    border-bottom: 1px solid var(--color-sub-3);
}
</style>
