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
                @keyup.enter="baTable.onSubmit()"
                v-model="baTable.form.items"
                label-position="right"
                :label-width="baTable.form.labelWidth + 'px'"
            >
                <el-form-item label="预览">
                    <el-image
                        class="preview-img"
                        :preview-src-list="[baTable.form.items!.full_url]"
                        :src="previewRenderFormatter(baTable.form.items!, {}, baTable.form.items!.suffix)"
                    ></el-image>
                </el-form-item>
                <el-form-item label="细目">
                    <el-input v-model="baTable.form.items!.topic" type="string" placeholder="文件保存目录，修改记录不会自动转移文件"></el-input>
                </el-form-item>
                <el-form-item label="物理路径">
                    <el-input v-model="baTable.form.items!.url" type="string" placeholder="文件保存路径，修改记录不会自动转移文件"></el-input>
                </el-form-item>
                <el-form-item label="图片宽度">
                    <el-input v-model="baTable.form.items!.width" type="number" placeholder="图片文件的宽度"></el-input>
                </el-form-item>
                <el-form-item label="图片高度">
                    <el-input v-model="baTable.form.items!.height" type="number" placeholder="图片文件的高度"></el-input>
                </el-form-item>
                <el-form-item label="原始名称">
                    <el-input v-model="baTable.form.items!.name" type="string" placeholder="文件原始名称"></el-input>
                </el-form-item>
                <el-form-item label="文件大小">
                    <el-input v-model="baTable.form.items!.size" type="number" placeholder="文件大小(bytes)"></el-input>
                </el-form-item>
                <el-form-item label="mime类型">
                    <el-input v-model="baTable.form.items!.mimetype" type="string" placeholder="文件mime类型"></el-input>
                </el-form-item>
                <el-form-item label="上传(引用)次数">
                    <el-input v-model="baTable.form.items!.quote" type="number" placeholder="此文件的上传(引用)次数"></el-input>
                    <span class="block-help">同一文件被多次上传时，只会保存一份和增加一条附件记录</span>
                </el-form-item>
                <el-form-item label="存储方式">
                    <el-input v-model="baTable.form.items!.storage" type="string" placeholder="存储方式"></el-input>
                </el-form-item>
                <el-form-item label="sha1编码">
                    <el-input v-model="baTable.form.items!.sha1" type="string" placeholder="文件的sha1编码"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">取消</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit()" type="primary">
                    {{ baTable.form.operateIds!.length > 1 ? '保存并编辑下一项' : '保存' }}
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

const baTable = inject('baTable') as baTableClass

const { t } = useI18n()
</script>

<style scoped lang="scss">
.preview-img {
    width: 60px;
    height: 60px;
}
</style>
