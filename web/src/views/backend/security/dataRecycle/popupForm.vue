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
                        :label="t('security.dataRecycle.Rule name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('security.dataRecycle.The rule name helps to identify deleted data later')"
                    />
                    <FormItem
                        :label="t('security.dataRecycle.controller')"
                        type="select"
                        v-model="baTable.form.items!.controller"
                        prop="controller"
                        :input-attr="{ content: baTable.form.extend!.controllerList }"
                        :placeholder="t('security.dataRecycle.The data collection mechanism will monitor delete operations under this controller')"
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
                            valueOnClear: '',
                        }"
                    />
                    <FormItem
                        :label="t('security.dataRecycle.Corresponding data sheet')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.data_table"
                        :key="baTable.form.items!.connection"
                        :input-attr="{
                            pk: 'table',
                            field: 'comment',
                            params: {
                                connection: baTable.form.items!.connection,
                                samePrefix: 1,
                                excludeTable: ['area', 'token', 'captcha', 'admin_group_access', 'user_money_log', 'user_score_log'],
                            },
                            remoteUrl: getTableListUrl,
                            onRow: onTableChange,
                        }"
                        prop="data_table"
                    />
                    <FormItem
                        :label="t('security.dataRecycle.Data table primary key')"
                        type="string"
                        v-model="baTable.form.items!.primary_key"
                        prop="primary_key"
                    />
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
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { getTablePk, getTableListUrl, getDatabaseConnectionListUrl } from '/@/api/common'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('security.dataRecycle.Rule name') })],
    controller: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('security.dataRecycle.controller') }),
        }),
    ],
    data_table: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: t('security.dataRecycle.data sheet') }),
        }),
    ],
    primary_key: [buildValidatorData({ name: 'required', trigger: 'change', title: t('security.dataRecycle.Data table primary key') })],
})

const onTableChange = () => {
    if (!baTable.form.items!.data_table) return
    getTablePk(baTable.form.items!.data_table, baTable.form.items!.connection).then((res) => {
        baTable.form.items!.primary_key = res.data.pk
        baTable.form.defaultItems!.primary_key = res.data.pk
    })
}
</script>

<style scoped lang="scss">
.ba-el-radio {
    margin-bottom: 10px;
}
</style>
