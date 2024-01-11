<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
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
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('examples.table.dialog2.string')"
                        type="string"
                        v-model="baTable.form.items!.string"
                        prop="string"
                        :placeholder="t('Please input field', { field: t('examples.table.dialog2.string') })"
                    />
                    <FormItem
                        :label="t('examples.table.dialog2.switch')"
                        type="switch"
                        v-model="baTable.form.items!.switch"
                        prop="switch"
                        :data="{ content: { '0': t('examples.table.dialog2.switch 0'), '1': t('examples.table.dialog2.switch 1') } }"
                    />
                    <FormItem
                        :label="t('examples.table.dialog2.datetime')"
                        type="datetime"
                        v-model="baTable.form.items!.datetime"
                        prop="datetime"
                        :placeholder="t('Please select field', { field: t('examples.table.dialog2.datetime') })"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    datetime: [buildValidatorData({ name: 'date', title: t('examples.table.dialog2.datetime') })],
    create_time: [buildValidatorData({ name: 'date', title: t('examples.table.dialog2.create_time') })],
})
</script>

<style scoped lang="scss"></style>
