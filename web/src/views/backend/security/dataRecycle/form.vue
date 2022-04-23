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
                    prop="name"
                    placeholder="规则名称有助于后续识别被删数据"
                />
                <FormItem
                    label="控制器"
                    type="select"
                    v-model="baTable.form.items!.controller"
                    prop="controller"
                    :data="{ content: formData.controllerList }"
                    placeholder="数据回收机制将监控此控制器下的删除操作"
                />
                <FormItem
                    label="对应数据表"
                    type="select"
                    v-model="baTable.form.items!.data_table"
                    prop="data_table"
                    :data="{ content: formData.tableList }"
                    :input-attr="{ onChange: onTableChange }"
                />
                <FormItem label="数据表主键" type="string" v-model="baTable.form.items!.primary_key" prop="primary_key" />
                <FormItem
                    label="状态"
                    type="radio"
                    v-model="baTable.form.items!.status"
                    prop="status"
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
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { getTablePk } from '/@/api/common'

interface Props {
    formData: {
        tableList?: anyObj
        controllerList?: anyObj
    }
}
const props = withDefaults(defineProps<Props>(), {
    formData: () => {
        return {}
    },
})

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData('required', '规则名称')],
    controller: [buildValidatorData('required', '', 'change', '请选择控制器')],
    data_table: [buildValidatorData('required', '', 'change', '请选择数据表')],
    primary_key: [buildValidatorData('required', '数据表主键', 'change')],
})

const onTableChange = (val: string) => {
    getTablePk(val).then((res) => {
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
