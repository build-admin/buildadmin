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
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
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
                    v-if="!baTable.form.loading"
                >
                    <FormItem
                        label="上级分组"
                        v-model="baTable.form.items!.pid"
                        type="remoteSelect"
                        :input-attr="{
                            params: { isTree: true },
                            field: 'name',
                            'remote-url': baTable.api.actionUrl.get('index'),
                            placeholder: '点击选择',
                        }"
                    />

                    <el-form-item prop="name" label="分组名称">
                        <el-input v-model="baTable.form.items!.name" type="string" placeholder="请输入分组名称"></el-input>
                    </el-form-item>
                    <el-form-item prop="auth" label="权限">
                        <el-tree
                            ref="treeRef"
                            :key="state.treeKey"
                            :default-checked-keys="state.defaultCheckedKeys"
                            :default-expand-all="true"
                            show-checkbox
                            node-key="id"
                            :props="{ children: 'children', label: 'title' }"
                            :data="state.menuRules"
                        />
                    </el-form-item>
                    <el-form-item label="状态">
                        <el-radio v-model="baTable.form.items!.status" label="0" :border="true">禁用</el-radio>
                        <el-radio v-model="baTable.form.items!.status" label="1" :border="true">启用</el-radio>
                    </el-form-item>
                </el-form>
            </div>
        </el-scrollbar>
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
import { reactive, ref, watch, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import { getMenuRules } from '/@/api/backend/auth/group'
import type { ElForm, ElTree, FormItemRule } from 'element-plus'
import { uuid } from '/@/utils/random'

interface MenuRules {
    id: number
    title: string
    children: MenuRules[]
}

const treeRef = ref<InstanceType<typeof ElTree>>()
const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const state: {
    treeKey: string
    defaultCheckedKeys: number[]
    menuRules: MenuRules[]
} = reactive({
    treeKey: uuid(),
    defaultCheckedKeys: [],
    menuRules: [],
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [
        {
            required: true,
            message: '请输入分组名称',
            trigger: 'blur',
        },
    ],
    auth: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                let ids = getCheckeds()
                if (ids.length <= 0) {
                    return callback(new Error('请选择权限'))
                }
                return callback()
            },
        },
    ],
})

getMenuRules().then((res) => {
    state.menuRules = res.data.list
})

const getCheckeds = () => {
    return treeRef.value!.getCheckedKeys().concat(treeRef.value!.getHalfCheckedKeys())
}

defineExpose({
    getCheckeds,
})

watch(
    () => baTable.form.items!.rules,
    () => {
        if (baTable.form.items!.rules && baTable.form.items!.rules.length) {
            if (baTable.form.items!.rules.includes('*')) {
                let arr: number[] = []
                for (const key in state.menuRules) {
                    arr.push(state.menuRules[key].id)
                }
                state.defaultCheckedKeys = arr
            } else {
                state.defaultCheckedKeys = baTable.form.items!.rules
            }
        } else {
            state.defaultCheckedKeys = []
        }
        state.treeKey = uuid()
    }
)
</script>

<style scoped lang="scss"></style>
