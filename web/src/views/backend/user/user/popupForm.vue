<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :destroy-on-close="true"
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
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <el-form-item prop="username" :label="t('user.user.User name')">
                        <el-input
                            v-model="baTable.form.items!.username"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.User name') + '(' + t('user.user.Login account') + ')' })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="nickname" :label="t('user.user.nickname')">
                        <el-input
                            v-model="baTable.form.items!.nickname"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.nickname') })"
                        ></el-input>
                    </el-form-item>
                    <FormItem
                        type="remoteSelect"
                        :label="t('user.user.group')"
                        v-model="baTable.form.items!.group_id"
                        prop="group_id"
                        :placeholder="t('user.user.group')"
                        :input-attr="{
                            params: { isTree: true, search: [{ field: 'status', val: '1', operator: 'eq' }] },
                            field: 'name',
                            remoteUrl: '/admin/user.Group/index',
                        }"
                    />
                    <FormItem :label="t('user.user.avatar')" type="image" v-model="baTable.form.items!.avatar" />
                    <el-form-item prop="email" :label="t('user.user.email')">
                        <el-input
                            v-model="baTable.form.items!.email"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.email') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="mobile" :label="t('user.user.mobile')">
                        <el-input
                            v-model="baTable.form.items!.mobile"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.mobile') })"
                        ></el-input>
                    </el-form-item>
                    <FormItem
                        :label="t('user.user.Gender')"
                        v-model="baTable.form.items!.gender"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { 0: t('Unknown'), 1: t('user.user.male'), 2: t('user.user.female') },
                        }"
                    />
                    <el-form-item :label="t('user.user.birthday')">
                        <el-date-picker
                            class="w100"
                            value-format="YYYY-MM-DD"
                            v-model="baTable.form.items!.birthday"
                            type="date"
                            :placeholder="t('Please select field', { field: t('user.user.birthday') })"
                        />
                    </el-form-item>
                    <el-form-item v-if="baTable.form.operate == 'Edit'" :label="t('user.user.balance')">
                        <el-input v-model="baTable.form.items!.money" readonly>
                            <template #append>
                                <el-button @click="changeAccount('money')">{{ t('user.user.Adjustment balance') }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item v-if="baTable.form.operate == 'Edit'" :label="t('user.user.integral')">
                        <el-input v-model="baTable.form.items!.score" readonly>
                            <template #append>
                                <el-button @click="changeAccount('score')">{{ t('user.user.Adjust integral') }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="password" :label="t('user.user.password')">
                        <el-input
                            v-model="baTable.form.items!.password"
                            type="password"
                            autocomplete="new-password"
                            :placeholder="
                                baTable.form.operate == 'Add'
                                    ? t('Please input field', { field: t('user.user.password') })
                                    : t('user.user.Please leave blank if not modified')
                            "
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="motto" :label="t('user.user.Personal signature')">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.motto"
                            type="textarea"
                            :placeholder="t('Please input field', { field: t('user.user.Personal signature') })"
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
import { regularPassword } from '/@/utils/validate'
import type { FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import router from '/@/router/index'
import { buildValidatorData } from '/@/utils/validate'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [buildValidatorData({ name: 'required', title: t('user.user.User name') }), buildValidatorData({ name: 'account' })],
    nickname: [buildValidatorData({ name: 'required', title: t('user.user.nickname') })],
    group_id: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('user.user.group') }) })],
    email: [buildValidatorData({ name: 'email', title: t('user.user.email') })],
    mobile: [buildValidatorData({ name: 'mobile' })],
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'Add') {
                    if (!val) {
                        return callback(new Error(t('Please input field', { field: t('user.user.password') })))
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

const changeAccount = (type: string) => {
    baTable.toggleForm()
    router.push({
        name: type == 'money' ? 'user/moneyLog' : 'user/scoreLog',
        query: {
            user_id: baTable.form.items!.id,
        },
    })
}

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
