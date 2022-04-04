<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
    >
        <template #title>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <div
            v-loading="baTable.form.loading"
            class="ba-operate-form"
            :class="'ba-' + baTable.form.operate + '-form'"
            :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
        >
            <el-form
                v-if="!baTable.form.loading"
                ref="formRef"
                @keyup.enter="baTable.onSubmit(formRef)"
                :model="baTable.form.items"
                label-position="right"
                :label-width="baTable.form.labelWidth + 'px'"
                :rules="rules"
            >
                <FormItem
                    label="规则名称"
                    type="string"
                    v-model="baTable.form.items!.name"
                    :attr="{ prop: 'name' }"
                    :input-attr="{ placeholder: '规则名称有助于后续识别被删数据' }"
                />
                <FormItem
                    label="控制器"
                    type="select"
                    v-model="baTable.form.items!.controller"
                    :attr="{ prop: 'controller' }"
                    :data="{ content: baTable.form.extend!.controllerList }"
                    :input-attr="{ placeholder: '数据监听机制将监控此控制器下的修改操作' }"
                />
                <FormItem
                    label="对应数据表"
                    type="select"
                    v-model="baTable.form.items!.data_table"
                    :attr="{ prop: 'data_table' }"
                    :data="{ content: baTable.form.extend!.tableList }"
                    :input-attr="{ onChange: baTable.onTableChange }"
                />
                <FormItem label="数据表主键" type="string" v-model="baTable.form.items!.primary_key" :attr="{ prop: 'primary_key' }" />
                <hr class="form-hr" />

                <FormItem
                    label="敏感字段"
                    type="selects"
                    v-model="baTable.form.items!.data_fields"
                    :key="baTable.form.extend!.fieldSelectKey"
                    :attr="{ prop: 'data_fields' }"
                    :data="{ content: baTable.form.extend!.fieldSelect }"
                    :input-attr="{ onChange: onFieldChange }"
                    v-loading="baTable.form.extend!.fieldLoading"
                />

                <FormItem
                    v-for="item in state.dataFields"
                    :label="item.name"
                    type="string"
                    v-model="item.value"
                    :input-attr="{ placeholder: '填写字段注释有助于后续快速识别字段' }"
                />

                <hr class="form-hr" />
                <FormItem
                    label="状态"
                    type="radio"
                    v-model="baTable.form.items!.status"
                    :attr="{ prop: 'status' }"
                    :data="{ content: { '0': '禁用', '1': '启用' } }"
                />
            </el-form>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">取消</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? '保存并编辑下一项' : '保存' }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type { sensitiveDataClass, DataFields } from './index'
import FormItem from '/@/components/formItem/index.vue'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'
import type { ElForm } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as sensitiveDataClass

const { t } = useI18n()

const state: {
    dataFields: DataFields[]
} = reactive({
    dataFields: [],
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData('required', '规则名称')],
    controller: [buildValidatorData('required', '', 'change', '请选择控制器')],
    data_table: [buildValidatorData('required', '', 'change', '请选择数据表')],
    primary_key: [buildValidatorData('required', '数据表主键', 'change')],
    data_fields: [buildValidatorData('required', '', 'blur', '请选择敏感字段')],
})

/**
 * 敏感数据字段更新
 * 保留原始输入，而又需要去掉已删除的字段
 */
const onFieldChange = (val: string[]) => {
    let dataFields: DataFields[] = []
    for (const key in val) {
        dataFields[key] =
            typeof state.dataFields[key] != 'undefined'
                ? state.dataFields[key]
                : {
                      name: val[key],
                      value: baTable.form.extend!.fieldlist[val[key]] ?? '',
                  }
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
