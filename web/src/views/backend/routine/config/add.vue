<template>
    <el-dialog class="ba-operate-dialog" :close-on-click-modal="false" :model-value="modelValue" @close="closeForm">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('routine.config.Add configuration item') }}
            </div>
        </template>
        <el-scrollbar class="ba-table-form-scrollbar">
            <div class="ba-operate-form ba-add-form" :style="'width: calc(100% - ' + state.labelWidth / 2 + 'px)'">
                <el-form
                    ref="formRef"
                    @keyup.enter="onAddSubmit(formRef)"
                    :rules="rules"
                    :model="state.addConfig"
                    :label-position="'left'"
                    :label-width="120"
                >
                    <FormItem :label="t('routine.config.Variable name')" type="string" v-model="state.addConfig.name" prop="name" />
                    <FormItem
                        :label="t('routine.config.Variable grouping')"
                        type="select"
                        v-model="state.addConfig.group"
                        prop="group"
                        :data="{ content: configGroup }"
                    />
                    <FormItem :label="t('routine.config.Variable title')" type="string" v-model="state.addConfig.title" prop="title" />
                    <FormItem :label="t('routine.config.Variable description')" type="string" v-model="state.addConfig.tip" />
                    <FormItem
                        :label="t('routine.config.Variable type')"
                        type="select"
                        v-model="state.addConfig.type"
                        prop="type"
                        :data="{ content: state.inputTypes }"
                        :input-attr="{ onChange: onTypeChange }"
                    />
                    <FormItem
                        class="add-item-content"
                        :label="t('routine.config.Dictionary data')"
                        type="textarea"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="onAddSubmit(formRef)"
                        v-model="state.addConfig.content"
                        :input-attr="{
                            rows: 3,
                            placeholder: t('routine.config.One line at a time, without quotation marks, for example: key1=value1'),
                        }"
                    />
                    <FormItem
                        :label="t('routine.config.Validation rules')"
                        type="selects"
                        v-model="state.addConfig.rule"
                        :data="{ content: validatorType }"
                    />
                    <FormItem
                        :label="t('routine.config.Extended properties')"
                        type="textarea"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="onAddSubmit(formRef)"
                        v-model="state.addConfig.extend"
                        :input-attr="{ placeholder: t('routine.config.One attribute per line without quotation marks') }"
                    />
                    <FormItem :label="t('weigh')" type="number" v-model.number="state.addConfig.weigh" prop="weigh" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + state.labelWidth / 1.8 + 'px)'">
                <el-button @click="closeForm">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="state.submitLoading" @click="onAddSubmit(formRef)" type="primary"> {{ t('add') }} </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { inputTypes } from '/@/components/baInput'
import { validatorType } from '/@/utils/validate'
import type { FormInstance, FormRules } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { postData } from '/@/api/backend/routine/config'
import { useI18n } from 'vue-i18n'

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

const { t } = useI18n()
const formRef = ref<FormInstance>()
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

const rules = reactive<FormRules>({
    name: [
        buildValidatorData({ name: 'required', title: t('routine.config.Variable name') }),
        buildValidatorData({ name: 'varName', message: t('Please enter the correct field', { field: t('routine.config.Variable name') }) }),
    ],
    group: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('routine.config.Variable grouping') }),
        }),
    ],
    title: [buildValidatorData({ name: 'required', title: t('routine.config.Variable title') })],
    type: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('routine.config.Variable type') }),
        }),
    ],
    weigh: [buildValidatorData({ name: 'integer', title: t('routine.config.number') })],
})

const inputTypesHandle = () => {
    let inputTypesKey: anyObj = {}
    for (const key in inputTypes) {
        if (inputTypes[key] != 'remoteSelect') {
            inputTypesKey[inputTypes[key]] = inputTypes[key]
        }
    }
    state.inputTypes = inputTypesKey
}

let needContent = ['radio', 'checkbox', 'select', 'selects']
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

const onAddSubmit = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            postData('add', state.addConfig).then((res) => {
                emits('update:modelValue', false)
            })
        }
    })
}

inputTypesHandle()
</script>

<style scoped lang="scss">
.add-item-content {
    display: none;
}
</style>
