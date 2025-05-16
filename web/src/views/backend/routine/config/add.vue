<template>
    <el-dialog class="ba-operate-dialog" :close-on-click-modal="false" :model-value="props.modelValue" @close="closeForm">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('routine.config.Add configuration item') }}
            </div>
        </template>
        <el-scrollbar class="ba-table-form-scrollbar">
            <div class="ba-operate-form ba-add-form" :style="config.layout.shrink ? '' : 'width: calc(100% - ' + state.labelWidth / 2 + 'px)'">
                <el-form
                    ref="formRef"
                    @keyup.enter="onAddSubmit()"
                    :rules="rules"
                    :model="{ ...state.addConfig, ...state.formItemData }"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="160"
                >
                    <FormItem
                        :label="t('routine.config.Variable group')"
                        type="select"
                        v-model="state.addConfig.group"
                        prop="group"
                        :input-attr="{ content: configGroup }"
                        :placeholder="t('Please select field', { field: t('routine.config.Variable group') })"
                    />
                    <CreateFormItemData v-model="state.formItemData" />
                    <FormItem :label="t('Weigh')" type="number" v-model="state.addConfig.weigh" prop="weigh" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + state.labelWidth / 1.8 + 'px)'">
                <el-button @click="closeForm">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="state.submitLoading" @click="onAddSubmit()" type="primary"> {{ t('Add') }} </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, useTemplateRef } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import type { FormRules } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { postData } from '/@/api/backend/routine/config'
import CreateFormItemData from '/@/components/formItem/createData.vue'
import { useI18n } from 'vue-i18n'
import { useConfig } from '/@/stores/config'

const config = useConfig()

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
const formRef = useTemplateRef('formRef')

const state: {
    inputTypes: anyObj
    labelWidth: number
    submitLoading: boolean
    addConfig: {
        group: string
        weigh: number
        content: string
    }
    formItemData: anyObj
} = reactive({
    inputTypes: {},
    labelWidth: 180,
    submitLoading: false,
    addConfig: {
        group: '',
        weigh: 0,
        content: '',
    },
    formItemData: {
        dict: `key1=value1
key2=value2`,
    },
})

const rules = reactive<FormRules>({
    group: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('routine.config.Variable group') }),
        }),
    ],
    name: [
        buildValidatorData({ name: 'required', title: t('routine.config.Variable name') }),
        buildValidatorData({ name: 'varName', message: t('Please enter the correct field', { field: t('routine.config.Variable name') }) }),
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

const onAddSubmit = () => {
    formRef.value?.validate((valid) => {
        if (valid) {
            state.addConfig.content = state.formItemData.dict
            delete state.formItemData.dict
            postData('add', { ...state.addConfig, ...state.formItemData }).then(() => {
                emits('update:modelValue', false)
            })
        }
    })
}
</script>

<style scoped lang="scss"></style>
