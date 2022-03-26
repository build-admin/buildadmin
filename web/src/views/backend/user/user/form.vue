<template>
    <!-- 对话框表单 -->
    <el-dialog
        top="5vh"
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
            >
                <el-form-item prop="username" label="用户名">
                    <el-input v-model="baTable.form.items!.username" type="string" placeholder="管理员登录名"></el-input>
                </el-form-item>
                <el-form-item prop="nickname" label="昵称">
                    <el-input v-model="baTable.form.items!.nickname" type="string" placeholder="请输入昵称"></el-input>
                </el-form-item>
                <el-form-item label="会员分组">
                    <remoteSelect
                        :params="{ isTree: true }"
                        field="name"
                        :remote-url="userGroup + 'index'"
                        v-model="baTable.form.items!.group_id"
                        placeholder="点击选择"
                    />
                </el-form-item>
                <el-form-item label="头像">
                    <el-upload
                        class="avatar-uploader"
                        action=""
                        :show-file-list="false"
                        @change="onAvatarBeforeUpload"
                        :auto-upload="false"
                        accept="image/gif, image/jpg, image/jpeg, image/bmp, image/png, image/webp"
                    >
                        <el-image :src="baTable.form.items!.avatar" class="avatar">
                            <template #error>
                                <div class="image-slot">
                                    <Icon size="30" color="#c0c4cc" name="el-icon-Picture" />
                                </div>
                            </template>
                        </el-image>
                    </el-upload>
                </el-form-item>
                <el-form-item prop="email" label="邮箱">
                    <el-input v-model="baTable.form.items!.email" type="string" placeholder="请输入邮箱"></el-input>
                </el-form-item>
                <el-form-item prop="mobile" label="手机号">
                    <el-input v-model="baTable.form.items!.mobile" type="string" placeholder="请输入手机号码"></el-input>
                </el-form-item>
                <el-form-item label="性别">
                    <el-radio v-model="baTable.form.items!.gender" :label="0" :border="true">未知</el-radio>
                    <el-radio v-model="baTable.form.items!.gender" :label="1" :border="true">男</el-radio>
                    <el-radio v-model="baTable.form.items!.gender" :label="2" :border="true">女</el-radio>
                </el-form-item>
                <el-form-item label="生日">
                    <el-date-picker
                        class="w100"
                        value-format="YYYY-MM-DD"
                        v-model="baTable.form.items!.birthday"
                        type="date"
                        placeholder="请选择生日"
                    />
                </el-form-item>
                <el-form-item v-if="baTable.form.operate == 'edit'" label="余额">
                    <el-input v-model="baTable.form.items!.money" readonly>
                        <template #append>
                            <el-button>调整余额</el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="baTable.form.operate == 'edit'" label="积分">
                    <el-input v-model="baTable.form.items!.score" readonly>
                        <template #append>
                            <el-button>调整积分</el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item prop="password" label="密码">
                    <el-input
                        v-model="baTable.form.items!.password"
                        type="password"
                        :placeholder="baTable.form.operate == 'add' ? '请输入密码' : '不修改请留空'"
                    ></el-input>
                </el-form-item>
                <el-form-item prop="motto" label="个性签名">
                    <el-input
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        v-model="baTable.form.items!.motto"
                        type="textarea"
                        placeholder="请输入个性签名"
                    ></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-radio v-model="baTable.form.items!.status" label="disable" :border="true">禁用</el-radio>
                    <el-radio v-model="baTable.form.items!.status" label="enable" :border="true">启用</el-radio>
                </el-form-item>
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
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { adminFileUpload } from '/@/api/common'
import type baTableClass from '/@/utils/baTable'
import { regularPassword, validatorAccount, validatorMobile } from '/@/utils/validate'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'
import type { ElForm } from 'element-plus'
import remoteSelect from '/@/components/remoteSelect/index.vue'
import { userGroup } from '/@/api/controllerUrls'

const formRef = ref<InstanceType<typeof ElForm>>()

const props = defineProps<{
    baTable: baTableClass
}>()

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
                if (props.baTable.form.operate == 'add') {
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

const onAvatarBeforeUpload = (file: any) => {
    let fd = new FormData()
    fd.append('file', file.raw)
    adminFileUpload(fd).then((res) => {
        if (res.code == 1) {
            props.baTable.form.items!.avatar = res.data.file.full_url
        }
    })
}
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
