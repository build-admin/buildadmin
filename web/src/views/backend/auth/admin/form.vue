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
                ref="formRef"
                @keyup.enter="baTable.onSubmit(formRef)"
                :model="baTable.form.items"
                label-position="right"
                :label-width="baTable.form.labelWidth + 'px'"
                :rules="rules"
                v-if="!baTable.form.loading"
            >
                <FormItem label="用户名" v-model="baTable.form.items!.username" type="string" prop="username" placeholder="管理员登录名" />
                <FormItem label="昵称" v-model="baTable.form.items!.nickname" type="string" prop="nickname" placeholder="请输入昵称" />
                <FormItem
                    label="管理员分组"
                    v-model="baTable.form.items!.group_arr"
                    type="remoteSelect"
                    :input-attr="{
                        multiple: true,
                        params: { isTree: true },
                        field: 'name',
                        'remote-url': authGroup + 'index',
                        placeholder: '点击选择',
                    }"
                />
                <FormItem label="头像" type="image" v-model="baTable.form.items!.avatar" />
                <FormItem label="邮箱" prop="email" v-model="baTable.form.items!.email" type="string" placeholder="请输入邮箱" />
                <FormItem label="手机号" prop="mobile" v-model="baTable.form.items!.mobile" type="string" placeholder="请输入手机号码" />
                <FormItem
                    label="密码"
                    prop="password"
                    v-model="baTable.form.items!.password"
                    type="password"
                    :placeholder="baTable.form.operate == 'add' ? '请输入密码' : '不修改请留空'"
                />
                <el-form-item prop="motto" label="个性签名">
                    <el-input
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        v-model="baTable.form.items!.motto"
                        type="textarea"
                        placeholder="请输入个性签名"
                    ></el-input>
                </el-form-item>
                <FormItem
                    label="状态"
                    v-model="baTable.form.items!.status"
                    type="radio"
                    :data="{ content: { '0': '禁用', '1': '启用' }, childrenAttr: { border: true } }"
                />
            </el-form>
        </div>
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
import { ref, reactive, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import { regularPassword, validatorAccount, validatorMobile } from '/@/utils/validate'
import type { ElForm, FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { authGroup } from '/@/api/controllerUrls'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [
        {
            required: true,
            message: '请输入用户名',
            trigger: 'blur',
        },
        {
            validator: validatorAccount,
            trigger: 'blur',
        },
    ],
    nickname: [
        {
            required: true,
            message: '请输入昵称',
            trigger: 'blur',
        },
    ],
    email: [
        {
            type: 'email',
            message: '请输入正确的邮箱',
            trigger: 'blur',
        },
    ],
    mobile: [
        {
            validator: validatorMobile,
            trigger: 'blur',
        },
    ],
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'add') {
                    if (!val) {
                        return callback(new Error('请输入密码'))
                    }
                } else {
                    if (!val) {
                        return callback()
                    }
                }
                if (!regularPassword(val)) {
                    return callback(new Error('请输入正确的密码'))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})
</script>

<style scoped lang="scss">
.avatar-uploader {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-radius: var(--el-border-radius-small);
    box-shadow: var(--el-box-shadow-light);
    border: 1px dashed var(--color-sub-1);
    cursor: pointer;
    overflow: hidden;
    width: 110px;
    height: 110px;
}
.avatar-uploader:hover {
    border-color: var(--color-primary);
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
