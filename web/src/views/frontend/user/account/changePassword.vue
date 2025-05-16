<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover" :header="t('user.account.changePassword.Change Password')">
            <div class="change-password">
                <el-form :model="state.form" :rules="state.rules" label-position="top" ref="formRef" @keyup.enter="onSubmit()">
                    <FormItem
                        :label="t('user.account.changePassword.Old password')"
                        type="password"
                        v-model="state.form.oldPassword"
                        prop="oldPassword"
                        :input-attr="{ showPassword: true }"
                        :placeholder="t('user.account.changePassword.Please enter your current password')"
                    />
                    <FormItem
                        :label="t('user.account.changePassword.New password')"
                        type="password"
                        v-model="state.form.newPassword"
                        prop="newPassword"
                        :input-attr="{ showPassword: true }"
                        :placeholder="t('Please input field', { field: t('user.account.changePassword.New password') })"
                    />
                    <FormItem
                        :label="t('user.account.changePassword.Confirm new password')"
                        type="password"
                        v-model="state.form.confirmPassword"
                        prop="confirmPassword"
                        :input-attr="{
                            showPassword: true,
                        }"
                        :placeholder="t('Please input field', { field: t('user.account.changePassword.Confirm new password') })"
                    />
                    <el-form-item class="submit-buttons">
                        <el-button @click="onResetForm(formRef)">{{ $t('Reset') }}</el-button>
                        <el-button type="primary" :loading="state.formSubmitLoading" @click="onSubmit()">{{ $t('Save') }}</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { reactive, useTemplateRef } from 'vue'
import { onResetForm } from '/@/utils/common'
import { buildValidatorData } from '/@/utils/validate'
import { changePassword } from '/@/api/frontend/user/index'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useUserInfo } from '/@/stores/userInfo'

const { t } = useI18n()

const userInfo = useUserInfo()
const formRef = useTemplateRef('formRef')

const state = reactive({
    formSubmitLoading: false,
    form: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: '',
    },
    rules: {
        oldPassword: [buildValidatorData({ name: 'required', title: t('user.account.changePassword.Old password') })],
        newPassword: [
            buildValidatorData({ name: 'required', title: t('user.account.changePassword.New password') }),
            buildValidatorData({ name: 'password' }),
        ],
        confirmPassword: [
            buildValidatorData({ name: 'required', title: t('user.account.changePassword.Confirm new password') }),
            buildValidatorData({ name: 'password' }),
            {
                validator: (rule: any, val: string, callback: Function) => {
                    if (state.form.newPassword || state.form.confirmPassword) {
                        if (state.form.newPassword == state.form.confirmPassword) {
                            callback()
                        } else {
                            callback(new Error(t('user.account.changePassword.The duplicate password does not match the new password')))
                        }
                    }
                    callback()
                },
                trigger: 'blur',
            },
        ],
    },
})

const onSubmit = () => {
    formRef.value?.validate((valid) => {
        if (valid) {
            state.formSubmitLoading = true
            changePassword(state.form)
                .then((res) => {
                    state.formSubmitLoading = false
                    if (res.code == 1) {
                        userInfo.logout()
                    }
                })
                .catch(() => {
                    state.formSubmitLoading = false
                })
        }
    })
}
</script>

<style scoped lang="scss">
.change-password {
    width: 360px;
    max-width: 100%;
}
.submit-buttons :deep(.el-form-item__content) {
    justify-content: flex-end;
}
</style>
