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
                        :label="t('auth.group.Parent group')"
                        v-model="baTable.form.items!.pid"
                        type="remoteSelect"
                        prop="pid"
                        :input-attr="{
                            params: { isTree: true },
                            field: 'name',
                            remoteUrl: baTable.api.actionUrl.get('index'),
                            placeholder: t('Click select'),
                            emptyValues: ['', null, undefined, 0],
                            valueOnClear: 0,
                        }"
                    />

                    <el-form-item prop="name" :label="t('auth.group.Group name')">
                        <el-input
                            v-model="baTable.form.items!.name"
                            type="string"
                            :placeholder="t('Please input field', { field: t('auth.group.Group name') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="auth" :label="t('auth.group.jurisdiction')">
                        <el-tree
                            ref="treeRef"
                            :key="baTable.form.extend!.treeKey"
                            :default-checked-keys="baTable.form.extend!.defaultCheckedKeys"
                            :default-expand-all="true"
                            show-checkbox
                            node-key="id"
                            :props="{ children: 'children', label: 'title', class: treeNodeClass }"
                            :data="baTable.form.extend!.menuRules"
                            class="w100"
                        />
                    </el-form-item>
                    <FormItem
                        :label="t('State')"
                        v-model="baTable.form.items!.status"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { 0: t('Disable'), 1: t('Enable') },
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
import { reactive, inject, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElTree, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import type Node from 'element-plus/es/components/tree/src/model/node'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const treeRef = useTemplateRef('treeRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('auth.group.Group name') })],
    auth: [
        {
            required: true,
            validator: (rule: any, val: string, callback: Function) => {
                let ids = getCheckeds()
                if (ids.length <= 0) {
                    return callback(new Error(t('Please select field', { field: t('auth.group.jurisdiction') })))
                }
                return callback()
            },
        },
    ],
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error(t('auth.group.The parent group cannot be the group itself')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})

const getCheckeds = () => {
    return treeRef.value!.getCheckedKeys().concat(treeRef.value!.getHalfCheckedKeys())
}

const treeNodeClass = (data: anyObj, node: Node) => {
    if (node.isLeaf) return ''
    let addClass = true
    for (const key in node.childNodes) {
        if (!node.childNodes[key].isLeaf) {
            addClass = false
        }
    }
    return addClass ? 'penultimate-node' : ''
}

defineExpose({
    getCheckeds,
})
</script>

<style scoped lang="scss">
:deep(.penultimate-node) {
    .el-tree-node__children {
        padding-left: 60px;
        white-space: pre-wrap;
        line-height: 12px;
        .el-tree-node {
            display: inline-block;
        }
        .el-tree-node__content {
            padding-left: 5px !important;
            padding-right: 5px;
            .el-tree-node__expand-icon {
                display: none;
            }
        }
    }
}
</style>
