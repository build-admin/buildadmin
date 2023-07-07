<!-- 多编辑器共存支持 -->
<!-- 所有编辑器的代码位于 /@/components/mixins/editor 文件夹，一个文件为一种编辑器，文件名则为编辑器名称 -->
<!-- 向本组件传递 editorType（文件名/编辑器名称）自动加载对应的编辑器进行渲染 -->
<template>
    <div>
        <component v-bind="$attrs" :is="mixins[state.editorType]" />
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import type { Component } from 'vue'

interface Props {
    editorType?: string
}

const props = withDefaults(defineProps<Props>(), {
    editorType: 'default',
})

const state = reactive({
    editorType: props.editorType,
})

const mixins: Record<string, Component> = {}
const mixinComponents: Record<string, any> = import.meta.glob('../../mixins/editor/**.vue', { eager: true })
for (const key in mixinComponents) {
    const fileName = key.replace('../../mixins/editor/', '').replace('.vue', '')
    mixins[fileName] = mixinComponents[key].default

    // 未安装富文本编辑器时，值为 default，安装之后，则值为最后一个编辑器的名称
    if (props.editorType == 'default' && fileName != 'default') {
        state.editorType = fileName
    }
}
</script>

<style scoped lang="scss"></style>
