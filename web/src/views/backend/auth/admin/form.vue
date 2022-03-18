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
            class="ba-operate-form"
            :class="'ba-' + baTable.form.operate + '-form'"
            :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
        >
            <el-form
                @keyup.enter="baTable.onSubmit"
                v-model="baTable.form.items"
                label-position="right"
                :label-width="baTable.form.labelWidth + 'px'"
            >
                <el-form-item label="用户名">
                    <el-input v-model="baTable.form.items!.username" type="string" placeholder="管理员登录名"></el-input>
                </el-form-item>
                <el-form-item label="昵称">
                    <el-input v-model="baTable.form.items!.nickname" type="string" placeholder="请输入昵称"></el-input>
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
                <el-form-item label="邮箱">
                    <el-input v-model="baTable.form.items!.email" type="string" placeholder="请输入邮箱"></el-input>
                </el-form-item>
                <el-form-item label="手机号">
                    <el-input v-model="baTable.form.items!.mobile" type="string" placeholder="请输入手机号码"></el-input>
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="baTable.form.items!.password" type="password" placeholder="不修改请留空"></el-input>
                </el-form-item>
                <el-form-item label="个性签名">
                    <el-input v-model="baTable.form.items!.motto" type="textarea" placeholder="请输入个性签名"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-radio v-model="baTable.form.items!.status" label="0" :border="true">禁用</el-radio>
                    <el-radio v-model="baTable.form.items!.status" label="1" :border="true">启用</el-radio>
                </el-form-item>
            </el-form>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">取消</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? '保存并编辑下一项' : '保存' }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { adminFileUpload } from '/@/api/common'
import type baTableClass from '/@/utils/baTable'

const props = defineProps<{
    baTable: baTableClass
}>()

const { t } = useI18n()

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
