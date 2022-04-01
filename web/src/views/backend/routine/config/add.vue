<template>
    <el-dialog custom-class="ba-operate-dialog" :close-on-click-modal="false" :model-value="modelValue" @close="closeForm">
        <template #title>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">增加配置项</div>
        </template>
        <div class="ba-operate-form ba-add-form" :style="'width: calc(100% - ' + state.labelWidth / 2 + 'px)'">
            <el-form ref="formRef" :rules="rules" :model="state.addConfig" :label-position="'left'" :label-width="120">
                <FormItem label="变量名" type="string" v-model="state.addConfig.name" :attr="{ prop: 'name' }" />
                <FormItem label="变量分组" type="select" v-model="state.addConfig.group" :attr="{ prop: 'group' }" :data="{ content: configGroup }" />
                <FormItem label="变量标题" type="string" v-model="state.addConfig.title" :attr="{ prop: 'title' }" />
                <FormItem label="变量描述" type="string" v-model="state.addConfig.tip" />
                <FormItem
                    label="变量类型"
                    type="select"
                    v-model="state.addConfig.type"
                    :attr="{ prop: 'type' }"
                    :data="{ content: state.inputTypes }"
                    :input-attr="{ onChange: onTypeChange }"
                />
                <FormItem
                    class="add-item-content"
                    label="字典数据"
                    type="textarea"
                    v-model="state.addConfig.content"
                    :input-attr="{ rows: 3, placeholder: '一行一个，无需引号，比如：key1=value1' }"
                />
                <FormItem label="验证规则" type="selects" v-model="state.addConfig.rule" :data="{ content: validatorType }" />
                <FormItem
                    label="扩展属性"
                    type="textarea"
                    v-model="state.addConfig.extend"
                    :input-attr="{ placeholder: '一行一个属性，无需引号，比如：class=config-item' }"
                />
                <FormItem label="权重" type="number" v-model="state.addConfig.weigh" :attr="{ prop: 'weigh' }" />
            </el-form>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + state.labelWidth / 1.8 + 'px)'">
                <el-button @click="closeForm">取消</el-button>
                <el-button v-blur :loading="state.submitLoading" @click="onAddSubmit(formRef)" type="primary"> 添加 </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { ElForm } from 'element-plus'
import { ref, reactive } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { inputTypes } from '/@/components/baInput'
import { validatorType } from '/@/utils/validate'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'

interface Props {
    modelValue: boolean
    configGroup: anyObj
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    configGroup: () => {
        return {}
    },
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

const closeForm = () => {
    emits('update:modelValue', false)
}

const formRef = ref<InstanceType<typeof ElForm>>()
const state: {
    inputTypes: anyObj
    labelWidth: number
    submitLoading: boolean
    addConfig: {
        name: string
        group: string
        title: string
        tip: string
        type: string
        content: string
        rule: string
        extend: string
        weigh: number
    }
} = reactive({
    inputTypes: {},
    labelWidth: 160,
    submitLoading: false,
    addConfig: {
        name: '',
        group: '',
        title: '',
        tip: '',
        type: '',
        content: `key1=value1
key2=value2`,
        rule: '',
        extend: '',
        weigh: 0,
    },
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [
        {
            required: true,
            message: '请输入变量名',
            trigger: 'blur',
        },
    ],
    group: [
        {
            required: true,
            message: '请选择分组',
            trigger: 'blur',
        },
    ],
    title: [
        {
            required: true,
            message: '请输入标题',
            trigger: 'blur',
        },
    ],
    type: [
        {
            required: true,
            message: '请选择类型',
            trigger: 'blur',
        },
    ],
    weigh: [
        {
            type: 'number',
            message: '请输入数字',
        },
    ],
})

const inputTypesHandle = () => {
    let inputTypesKey: anyObj = {}
    for (const key in inputTypes) {
        inputTypesKey[inputTypes[key]] = inputTypes[key]
    }
    state.inputTypes = inputTypesKey
}

let needContent = ['radio', 'checkbox', 'array', 'select', 'selects']
const onTypeChange = (value: string) => {
    let contentEl = document.querySelector('.add-item-content') as HTMLElement
    if (!contentEl) {
        return
    }
    if (needContent.includes(value)) {
        contentEl.style.display = 'flex'
    } else {
        contentEl.style.display = 'none'
    }
}

const onAddSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {
    console.log(state.addConfig)
}

inputTypesHandle()
</script>

<style scoped lang="scss">
.add-item-content {
    display: none;
}
</style>
