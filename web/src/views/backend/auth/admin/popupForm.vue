<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        :destroy-on-close="true"
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
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <FormItem
                        :label="t('auth.admin.username')"
                        v-model="baTable.form.items!.username"
                        type="string"
                        prop="username"
                        :placeholder="t('auth.admin.Administrator login')"
                    />
                    <FormItem
                        :label="t('auth.admin.nickname')"
                        v-model="baTable.form.items!.nickname"
                        type="string"
                        prop="nickname"
                        :placeholder="t('Please input field', { field: t('auth.admin.nickname') })"
                    />
                    <FormItem
                        :label="t('auth.admin.group')"
                        v-model="baTable.form.items!.group_arr"
                        prop="group_arr"
                        type="remoteSelect"
                        :key="'group-' + baTable.form.items!.id"
                        :input-attr="{
                            multiple: true,
                            params: { isTree: true, absoluteAuth: adminInfo.id == baTable.form.items!.id ? 0 : 1 },
                            field: 'name',
                            remoteUrl: '/admin/auth.Group/index',
                            placeholder: t('Click select'),
                        }"
                    />
                    <FormItem :label="t('auth.admin.avatar')" type="image" v-model="baTable.form.items!.avatar" />
                    <FormItem
                        :label="t('auth.admin.email')"
                        prop="email"
                        v-model="baTable.form.items!.email"
                        type="string"
                        :placeholder="t('Please input field', { field: t('auth.admin.email') })"
                    />
                    <FormItem
                        :label="t('auth.admin.mobile')"
                        prop="mobile"
                        v-model="baTable.form.items!.mobile"
                        type="string"
                        :placeholder="t('Please input field', { field: t('auth.admin.mobile') })"
                    />
                    <FormItem
                        :label="t('auth.admin.Password')"
                        prop="password"
                        v-model="baTable.form.items!.password"
                        type="password"
                        :input-attr="{ autocomplete: 'new-password' }"
                        :placeholder="
                            baTable.form.operate == 'Add'
                                ? t('Please input field', { field: t('auth.admin.Password') })
                                : t('auth.admin.Please leave blank if not modified')
                        "
                    />
                    <el-form-item prop="motto" :label="t('auth.admin.Personal signature')">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.motto"
                            type="textarea"
                            :placeholder="t('Please input field', { field: t('auth.admin.Personal signature') })"
                        ></el-input>
                    </el-form-item>
                    <FormItem
                        :label="t('State')"
                        v-model="baTable.form.items!.status"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { disable: t('Disable'), enable: t('Enable') },
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
import { reactive, inject, watch, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import { regularPassword, buildValidatorData } from '/@/utils/validate'
import type { FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const adminInfo = useAdminInfo()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [buildValidatorData({ name: 'required', title: t('auth.admin.username') }), buildValidatorData({ name: 'account' })],
    nickname: [buildValidatorData({ name: 'required', title: t('auth.admin.nickname') })],
    group_arr: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('auth.admin.group') }) })],
    email: [buildValidatorData({ name: 'email', message: t('Please enter the correct field', { field: t('auth.admin.email') }) })],
    mobile: [buildValidatorData({ name: 'mobile', message: t('Please enter the correct field', { field: t('auth.admin.mobile') }) })],
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'Add') {
                    if (!val) {
                        return callback(new Error(t('Please input field', { field: t('auth.admin.Password') })))
                    }
                } else {
                    if (!val) {
                        return callback()
                    }
                }
                if (!regularPassword(val)) {
                    return callback(new Error(t('validate.Please enter the correct password')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})

watch(
    () => baTable.form.operate,
    (newVal) => {
        rules.password![0].required = newVal == 'Add'
    }
)
</script>

<style scoped lang="scss">
.avatar-uploader {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-radius: var(--el-border-radius-small);
    box-shadow: var(--el-box-shadow-light);
    border: 1px dashed var(--el-border-color);
    cursor: pointer;
    overflow: hidden;
    width: 110px;
    height: 110px;
}
.avatar-uploader:hover {
    border-color: var(--el-color-primary);
}
.avatar {
    width: 110px;
    height: 110px;
    display: block;
}
.image-slot {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
</style>
