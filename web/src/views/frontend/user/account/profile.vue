<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>{{ $t('user.user.personal data') }}</span>
                    <el-button @click="router.push({ name: 'account/changePassword' })" type="info" v-blur plain>{{
                        $t('user.user.Change Password')
                    }}</el-button>
                </div>
            </template>
            <div class="user-profile">
                <el-form :model="state.form" :rules="state.rules" :label-position="'top'" ref="formRef" @keyup.enter="onSubmit(formRef)">
                    <FormItem :label="$t('user.user.head portrait')" type="image" v-model="state.form.avatar" prop="avatar" />
                    <FormItem
                        :label="$t('user.user.User name')"
                        type="string"
                        v-model="state.form.username"
                        :input-attr="{
                            disabled: true,
                        }"
                        :placeholder="$t('Please input field', { field: $t('user.user.User name') })"
                    />
                    <FormItem
                        :label="$t('user.user.User nickname')"
                        type="string"
                        v-model="state.form.nickname"
                        :placeholder="$t('Please input field', { field: $t('user.user.User nickname') })"
                        prop="nickname"
                    />
                    <!-- <el-form-item label="电子邮箱">
                        <el-input v-model="state.form.email" readonly placeholder="请输入电子邮箱">
                            <template #append>
                                <el-button type="primary">{{ state.form.email ? '点击修改' : '绑定' }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item> -->
                    <!-- <el-form-item label="手机号码">
                        <el-input v-model="state.form.mobile" readonly placeholder="请输入手机号码">
                            <template #append>
                                <el-button type="primary">{{ state.form.mobile ? '点击修改' : '绑定' }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item> -->
                    <FormItem
                        :label="$t('user.user.Gender')"
                        type="radio"
                        v-model="state.form.gender"
                        :data="{
                            childrenAttr: { border: true },
                            content: { '0': $t('user.user.secrecy'), '1': $t('user.user.male'), '2': $t('user.user.female') },
                        }"
                    />
                    <FormItem :label="$t('user.user.birthday')" type="date" v-model="state.form.birthday" />
                    <FormItem
                        :label="$t('user.user.Personal signature')"
                        type="textarea"
                        :placeholder="$t('Please input field', { field: $t('user.user.Personal signature') })"
                        v-model="state.form.motto"
                        :input-attr="{ 'show-word-limit': true, rows: 3 }"
                    />
                    <el-form-item class="submit-buttons">
                        <el-button @click="onResetForm(formRef)">{{ $t('Reset') }}</el-button>
                        <el-button type="primary" :loading="state.formSubmitLoading" @click="onSubmit(formRef)">{{ $t('Save') }}</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { FormInstance } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { useUserInfo } from '/@/stores/userInfo'
import { onResetForm } from '/@/utils/common'
import { buildValidatorData } from '/@/utils/validate'
import { postProfile } from '/@/api/frontend/user/index'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const userInfo = useUserInfo()

const formRef = ref<FormInstance>()
const state = reactive({
    formSubmitLoading: false,
    form: userInfo.$state,
    rules: {
        avatar: [buildValidatorData('required', '', 'blur', t('Please select field', { field: t('user.user.head portrait') }))],
        nickname: [buildValidatorData('required', t('user.user.nickname'))],
    },
})

const onSubmit = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            state.formSubmitLoading = true
            postProfile(state.form)
                .then((res) => {
                    state.formSubmitLoading = false
                })
                .catch((err) => {
                    state.formSubmitLoading = false
                })
        }
    })
}
</script>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.user-profile {
    width: 360px;
    max-width: 100%;
}
.submit-buttons :deep(.el-form-item__content) {
    justify-content: flex-end;
}
:deep(.el-upload-list--picture-card) {
    --el-upload-list-picture-card-size: 100px;
}
:deep(.el-upload--picture-card) {
    --el-upload-picture-card-size: 100px;
}
</style>
