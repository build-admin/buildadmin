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
                    <el-form-item label="当前操作标识符号">
                        <div>{{ baTable.form.operate }}</div>
                    </el-form-item>
                    <el-form-item label="添加提示" v-if="baTable.form.operate == 'Add'">
                        <div>如您所见，添加表单项均可设置默认值，不论类型</div>
                    </el-form-item>
                    <FormItem
                        :label="t('examples.table.form.other.string')"
                        type="string"
                        v-model="baTable.form.items!.string"
                        prop="string"
                        :placeholder="t('Please input field', { field: t('examples.table.form.other.string') })"
                    />
                    <FormItem
                        :label="t('examples.table.form.other.number')"
                        type="number"
                        prop="number"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.number"
                        :placeholder="t('Please input field', { field: t('examples.table.form.other.number') })"
                    />
                    <FormItem
                        :label="t('examples.table.form.other.datetime')"
                        type="datetime"
                        v-model="baTable.form.items!.datetime"
                        prop="datetime"
                        :placeholder="t('Please select field', { field: t('examples.table.form.other.datetime') })"
                    />
                    <FormItem :label="t('examples.table.form.other.image')" type="image" v-model="baTable.form.items!.image" prop="image" />
                    <FormItem
                        :label="t('examples.table.form.other.user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.user_id"
                        prop="user_id"
                        :input-attr="{ pk: 'ba_user.id', field: 'username', 'remote-url': '/admin/user.User/index' }"
                        :placeholder="t('Please select field', { field: t('examples.table.form.other.user_id') })"
                    />

                    <!-- 示例核心代码(2/3) -->
                    <el-form-item label="baTable使用细节">
                        <el-link type="success" @click="baTable.form.submitLoading = !baTable.form.submitLoading"
                            >可随意改属性 - ({{ baTable.form.submitLoading ? '取消 loading' : '点击让提交按钮 loading' }})</el-link
                        >
                    </el-form-item>

                    <!-- 示例核心代码(3/3) -->
                    <!-- 请继续查阅 onTransform 函数 -->
                    <el-form-item label="baTable使用细节">
                        <el-link type="success" @click="onTransform">可随意改的属性包括很多</el-link>
                    </el-form-item>
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

const onTransform = () => {
    if (!baTable.table.extend?.transform) {
        // 表单项
        // 您甚至可以直接在次关闭掉表单，或者请求另外一个数据进行编辑
        baTable.form.items!.string = '我是字符串添加时默认值 - 改'
        baTable.form.items!.number = 88
        baTable.form.items!.datetime = '2023-08-18 08:08:08'
        baTable.form.items!.image = ''

        // 修改每页显示条数，修改后需执行 baTable.onTableHeaderAction('refresh', {}) 重新获取数据
        baTable.table.filter!.limit = 50
        // baTable.onTableHeaderAction('refresh', {})

        baTable.table.showComSearch = true

        // ...
    } else {
        baTable.form.items!.string = '我是字符串添加时默认值'
        baTable.form.items!.number = 66
        baTable.form.items!.datetime = '2023-08-08 06:06:06'
        baTable.form.items!.image = '/static/images/avatar.png'

        baTable.table.filter!.limit = 10

        baTable.table.showComSearch = false
    }

    // baTable的扩展数据，由开发者随心使用
    baTable.table.extend!.transform = !baTable.table.extend!.transform
    // baTable.form.extend
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    number: [buildValidatorData({ name: 'number', title: t('examples.table.form.other.number') })],
    datetime: [buildValidatorData({ name: 'date', title: t('examples.table.form.other.datetime') })],
    create_time: [buildValidatorData({ name: 'date', title: t('examples.table.form.other.create_time') })],
})
</script>

<style scoped lang="scss"></style>
