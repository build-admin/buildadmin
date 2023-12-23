<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
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
                    @keyup.enter="baTable.onSubmit()"
                    v-model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                >
                    <el-form-item :label="t('utils.preview')">
                        <el-image
                            class="preview-img"
                            :preview-src-list="[baTable.form.items!.full_url]"
                            :src="previewRenderFormatter(baTable.form.items!, {}, baTable.form.items!.suffix)"
                        ></el-image>
                    </el-form-item>
                    <el-form-item :label="t('utils.Breakdown')">
                        <el-input
                            v-model="baTable.form.items!.topic"
                            type="string"
                            :placeholder="
                                t(
                                    'routine.attachment.The file is saved in the directory, and the file will not be automatically transferred if the record is modified'
                                )
                            "
                            readonly
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.Physical path')">
                        <el-input
                            v-model="baTable.form.items!.url"
                            type="string"
                            :placeholder="t('routine.attachment.File saving path Modifying records will not automatically transfer files')"
                            readonly
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.image width')">
                        <el-input
                            v-model="baTable.form.items!.width"
                            type="number"
                            :placeholder="t('routine.attachment.Width of picture file')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.Picture height')">
                        <el-input
                            v-model="baTable.form.items!.height"
                            type="number"
                            :placeholder="t('routine.attachment.Height of picture file')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('utils.Original name')">
                        <el-input
                            v-model="baTable.form.items!.name"
                            type="string"
                            :placeholder="t('routine.attachment.Original file name')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.file size')">
                        <el-input
                            v-model="baTable.form.items!.size"
                            type="number"
                            :placeholder="t('routine.attachment.File size (bytes)')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.mime type')">
                        <el-input
                            v-model="baTable.form.items!.mimetype"
                            type="string"
                            :placeholder="t('routine.attachment.File MIME type')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('utils.Upload (Reference) times')">
                        <el-input
                            v-model="baTable.form.items!.quote"
                            type="number"
                            :placeholder="t('routine.attachment.Upload (Reference) times of this file')"
                        ></el-input>
                        <span class="block-help">
                            {{
                                t(
                                    'routine.attachment.When the same file is uploaded multiple times, only one attachment record will be saved and added'
                                )
                            }}
                        </span>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.Storage mode')">
                        <el-input
                            v-model="baTable.form.items!.storage"
                            type="string"
                            :placeholder="t('routine.attachment.Storage mode')"
                            readonly
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('routine.attachment.SHA1 code')">
                        <el-input
                            v-model="baTable.form.items!.sha1"
                            type="string"
                            :placeholder="t('routine.attachment.SHA1 encoding of file')"
                            readonly
                        ></el-input>
                    </el-form-item>
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit()" type="primary">
                    {{ baTable.form.operateIds!.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import { previewRenderFormatter } from './index'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()
</script>

<style scoped lang="scss">
.preview-img {
    width: 60px;
    height: 60px;
}
</style>
