<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('security.sensitiveData.Rule name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('security.sensitiveData.The rule name helps to identify the modified data later')"
                    />
                    <FormItem
                        :label="t('security.sensitiveData.controller')"
                        type="select"
                        v-model="baTable.form.items!.controller"
                        prop="controller"
                        :input-attr="{ content: baTable.form.extend!.controllerList }"
                        :placeholder="
                            t('security.sensitiveData.The data listening mechanism will monitor the modification operations under this controller')
                        "
                    />
                    <FormItem
                        :label="t('Database connection')"
                        v-model="baTable.form.items!.connection"
                        type="remoteSelect"
                        :block-help="t('Database connection help')"
                        :input-attr="{
                            pk: 'key',
                            field: 'key',
                            remoteUrl: getDatabaseConnectionListUrl,
                            onChange: baTable.onConnectionChange,
                            valueOnClear: '',
                        }"
                    />
                    <FormItem
                        :label="t('security.sensitiveData.Corresponding data sheet')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.data_table"
                        :key="baTable.form.items!.connection"
                        :input-attr="{
                            pk: 'table',
                            field: 'comment',
                            params: {
                                connection: baTable.form.items!.connection,
                                samePrefix: 1,
                                excludeTable: ['area', 'token', 'captcha', 'admin_group_access', 'admin_log', 'user_money_log', 'user_score_log'],
                            },
                            remoteUrl: getTableListUrl,
                            onChange: baTable.onTableChange,
                        }"
                        prop="data_table"
                    />
                    <FormItem
                        :label="t('security.sensitiveData.Data table primary key')"
                        type="string"
                        v-model="baTable.form.items!.primary_key"
                        prop="primary_key"
                    />
                    <template v-if="!isEmpty(baTable.form.extend!.fieldSelect)">
                        <hr class="form-hr" />

                        <FormItem
                            :label="t('security.sensitiveData.Sensitive fields')"
                            type="selects"
                            v-model="baTable.form.items!.data_fields"
                            :key="baTable.form.extend!.fieldSelectKey"
                            prop="data_fields"
                            :input-attr="{
                                onChange: onFieldChange,
                                content: baTable.form.extend!.fieldSelect,
                            }"
                            v-loading="baTable.form.extend!.fieldLoading"
                        />

                        <FormItem
                            v-for="(item, idx) in state.dataFields"
                            :key="idx"
                            :label="item.name"
                            type="string"
                            v-model="item.value"
                            :tip="t('security.sensitiveData.Filling in field notes helps you quickly identify fields later')"
                        />

                        <hr class="form-hr" />
                    </template>
                    <FormItem
                        :label="t('State')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :input-attr="{
                            border: true,
                            content: { 0: t('Disable'), 1: t('Enable') },
                        }"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, inject, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import type { sensitiveDataClass, DataFields } from './index'
import FormItem from '/@/components/formItem/index.vue'
import type { FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { useConfig } from '/@/stores/config'
import { getTableListUrl, getDatabaseConnectionListUrl } from '/@/api/common'
import { isEmpty } from 'lodash-es'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as sensitiveDataClass

const { t } = useI18n()

const state: {
    dataFields: DataFields[]
} = reactive({
    dataFields: [],
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('security.sensitiveData.Rule name') })],
    controller: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('security.sensitiveData.controller') }),
        }),
    ],
    data_table: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('security.sensitiveData.data sheet') }),
        }),
    ],
    primary_key: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            title: t('security.sensitiveData.Data table primary key'),
        }),
    ],
    data_fields: [
        buildValidatorData({
            name: 'required',
            message: t('Please select field', { field: t('security.sensitiveData.Sensitive fields') }),
        }),
    ],
})

/**
 * 敏感数据字段更新
 * 保留原始输入，而又需要去掉已删除的字段
 */
const onFieldChange = (val: string[]) => {
    let dataFields: DataFields[] = []
    for (const key in val) {
        let exist: boolean | DataFields = false
        for (const k in state.dataFields) {
            if (state.dataFields[k].name == val[key]) {
                exist = state.dataFields[k]
            }
        }
        dataFields[key] = exist ? exist : { name: val[key], value: baTable.form.extend!.fieldList[val[key]] ?? '' }
    }
    state.dataFields = dataFields
}

const getDataFields = () => {
    return state.dataFields
}

const setDataFields = (dataFields: DataFields[]) => {
    state.dataFields = dataFields
}

defineExpose({
    getDataFields,
    setDataFields,
})
</script>

<style scoped lang="scss">
.ba-el-radio {
    margin-bottom: 10px;
}
.form-hr {
    border-color: #dcdfe6;
    border-style: solid;
    margin-bottom: 16px;
}
</style>
