<!-- 一个用于创建 FormItem 数据的组件 -->
<!-- 使用场景举例：系统配置->添加配置项 -->
<template>
    <div>
        <!-- 本组件不被 el-form 包含，方便您在其他 el-form 的任意位置使用，且没有带了一个 el-form 时会出现的负担 -->
        <!-- formitem 已经设置了 prop 属性，以便外部 el-form 添加表单验证规则 -->
        <FormItem
            v-if="form.name.show"
            :label="form.name.title"
            type="string"
            v-model="form.name.value"
            :placeholder="t('Please input field', { field: form.name.title })"
            :input-attr="{
                onChange: updateValue,
            }"
            prop="name"
        />
        <FormItem
            v-if="form.title.show"
            :label="form.title.title"
            type="string"
            v-model="form.title.value"
            :placeholder="t('Please input field', { field: form.title.title })"
            :input-attr="{
                onChange: updateValue,
            }"
            prop="title"
        />
        <FormItem
            v-if="form.type.show"
            :label="form.type.title"
            type="select"
            v-model="form.type.value"
            :data="{ content: state.inputTypes }"
            :placeholder="t('Please select field', { field: form.type.title })"
            :input-attr="{
                onChange: updateValue,
            }"
            prop="type"
        />
        <FormItem
            v-if="form.dict.show && ['radio', 'checkbox', 'select', 'selects'].includes(form.type.value!)"
            :label="form.dict.title"
            type="textarea"
            v-model="form.dict.value"
            :input-attr="{
                rows: 3,
                placeholder: t('utils.One line at a time, without quotation marks, for example: key1=value1'),
                onChange: updateValue,
            }"
            prop="dict"
            @keyup.enter.stop=""
        />
        <FormItem
            v-if="form.tip.show"
            :label="form.tip.title"
            type="string"
            v-model="form.tip.value"
            :placeholder="t('Please input field', { field: form.tip.title })"
            :input-attr="{
                onChange: updateValue,
            }"
            prop="tip"
        />
        <FormItem
            v-if="form.rule.show"
            :label="form.rule.title"
            type="selects"
            v-model="form.rule.value"
            :data="{ content: state.validators }"
            :placeholder="t('Please select field', { field: form.rule.title })"
            :input-attr="{
                onChange: updateValue,
            }"
            prop="rule"
        />
        <FormItem
            v-if="form.extend.show"
            :label="form.extend.title"
            type="textarea"
            v-model="form.extend.value"
            :input-attr="{
                onChange: updateValue,
                placeholder: t('utils.One attribute per line without quotation marks(formitem)'),
            }"
            prop="extend"
            @keyup.enter.stop=""
        />
        <FormItem
            v-if="form.inputExtend.show"
            :label="form.inputExtend.title"
            type="textarea"
            v-model="form.inputExtend.value"
            :input-attr="{
                onChange: updateValue,
                placeholder: t('utils.Extended properties of Input, one line without quotation marks, such as: size=large'),
            }"
            prop="inputExtend"
            @keyup.enter.stop=""
        />
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { inputTypes } from '/@/components/baInput'
import { validatorType } from '/@/utils/validate'
import { i18n } from '/@/lang'

const { t } = i18n.global

type OptionItem = {
    // 是否显示（被创建的数据是否需要这一项）
    show?: boolean
    // 被创建数据的标题（默认使用：props.dataTitle + 本 title，自定义此 title 后，单使用此 title）
    title?: string
}

type ValidatesOptionItem = Omit<OptionItem, 'value'> & {
    value?: string[]
}

interface Props {
    // 被创建数据的标题，作为所有表单项的前缀（默认值）
    dataTitle?: string
    // 默认值
    modelValue: {
        name?: string
        title?: string
        type?: string
        tip?: string
        rule?: string[]
        extend?: string
        dict?: string
        inputExtend?: string
    }
    // 表单项配置
    options?: {
        // 变量名
        name?: OptionItem
        // 标题
        title?: OptionItem
        // 类型
        type?: OptionItem
        // 提示信息
        tip?: OptionItem
        // 验证规则
        rule?: ValidatesOptionItem
        // FormItem 的扩展属性
        extend?: OptionItem
        // 字典数据（单选、复选等类型的字典）
        dict?: OptionItem
        // Input 的扩展属性
        inputExtend?: OptionItem
    }
    excludeInputTypes?: string[]
    excludeValidatorRule?: string[]
}

const props = withDefaults(defineProps<Props>(), {
    dataTitle: i18n.global.t('utils.Var'),
    modelValue: () => {
        return {
            name: '',
            title: '',
            type: '',
            tip: '',
            rule: [],
            extend: '',
            dict: '',
            inputExtend: '',
        }
    },
    name: () => {
        return {}
    },
    title: () => {
        return {}
    },
    type: () => {
        return {}
    },
    tip: () => {
        return {}
    },
    rule: () => {
        return {}
    },
    extend: () => {
        return {}
    },
    dict: () => {
        return {}
    },
    inputExtend: () => {
        return {}
    },
    excludeInputTypes: () => [],
    excludeValidatorRule: () => [],
})

const form = reactive({
    name: {
        show: props.options?.name?.show === false ? false : true,
        value: props.modelValue.name,
        title: props.options?.name?.title ?? props.dataTitle + t('utils.Name'), // 变量名
    },
    title: {
        show: props.options?.title?.show === false ? false : true,
        value: props.modelValue.title,
        title: props.options?.title?.title ?? props.dataTitle + t('utils.Title'), // 变量标题
    },
    type: {
        show: props.options?.type?.show === false ? false : true,
        value: props.modelValue.type,
        title: props.options?.type?.title ?? props.dataTitle + t('utils.type'), // 变量类型
    },
    tip: {
        show: props.options?.tip?.show === false ? false : true,
        value: props.modelValue.tip,
        title: props.options?.tip?.title ?? t('utils.Tip'), // 提示信息
    },
    rule: {
        show: props.options?.rule?.show === false ? false : true,
        value: props.modelValue.rule,
        title: props.options?.rule?.title ?? t('utils.Rule'), // 验证规则
    },
    extend: {
        show: props.options?.extend?.show === false ? false : true,
        value: props.modelValue.extend,
        title: props.options?.extend?.title ?? 'FormItem ' + t('utils.Extend'), // FormItem 扩展属性
    },
    dict: {
        show: props.options?.dict?.show === false ? false : true,
        value: props.modelValue.dict,
        title: props.options?.dict?.title ?? t('utils.Dict'), // 字典数据
    },
    inputExtend: {
        show: props.options?.inputExtend?.show === false ? false : true,
        value: props.modelValue.inputExtend,
        title: props.options?.inputExtend?.title ?? 'Input ' + t('utils.Extend'), // Input 扩展属性
    },
})

const state = reactive({
    validators: {},
    inputTypes: {},
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: Props['modelValue']): void
}>()

const updateValue = () => {
    emits('update:modelValue', {
        name: form.name.value ?? '',
        title: form.title.value ?? '',
        type: form.type.value ?? '',
        tip: form.tip.value ?? '',
        rule: form.rule.value ?? [],
        extend: form.extend.value ?? '',
        dict: form.dict.value ?? '',
        inputExtend: form.inputExtend.value ?? '',
    })
}

const dataPretreatment = () => {
    let inputTypesKey: anyObj = {}
    for (const key in inputTypes) {
        if (!props.excludeInputTypes.includes(inputTypes[key])) {
            inputTypesKey[inputTypes[key]] = inputTypes[key]
        }
    }
    state.inputTypes = inputTypesKey

    let validators: anyObj = {}
    for (const key in validatorType) {
        if (!props.excludeValidatorRule.includes(key)) {
            validators[key] = validatorType[key as keyof typeof validatorType]
        }
    }
    state.validators = validators

    updateValue()
}

dataPretreatment()
</script>

<style scoped lang="scss"></style>
