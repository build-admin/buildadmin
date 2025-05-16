<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('user.group.GroupName') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { getUserRules } from '/@/api/backend/user/group'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { uuid } from '/@/utils/random'

defineOptions({
    name: 'user/group',
})

const { t } = useI18n()
const formRef = useTemplateRef('formRef')
const tableRef = useTemplateRef('tableRef')

const baTable = new baTableClass(
    new baTableApi('/admin/user.Group/'),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('Id'), prop: 'id', align: 'center', operator: '=', operatorPlaceholder: t('Id'), width: 70 },
            { label: t('user.group.Group name'), prop: 'name', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { 0: 'danger', 1: 'success' },
                replaceValue: { 0: t('Disable'), 1: t('Enable') },
            },
            { label: t('Update time'), prop: 'update_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('Create time'), prop: 'create_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: t('Operate'),
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {
            status: 1,
        },
    }
)

// 利用提交前钩子重写提交操作
baTable.before.onSubmit = ({ formEl, operate, items }) => {
    let submitCallback = () => {
        baTable.form.submitLoading = true
        baTable.api
            .postData(operate, {
                ...items,
                rules: formRef.value?.getCheckeds(),
            })
            .then((res) => {
                baTable.onTableHeaderAction('refresh', {})
                baTable.form.submitLoading = false
                baTable.form.operateIds?.shift()
                if (baTable.form.operateIds!.length > 0) {
                    baTable.toggleForm('Edit', baTable.form.operateIds)
                } else {
                    baTable.toggleForm()
                }
                baTable.runAfter('onSubmit', { res })
            })
            .catch(() => {
                baTable.form.submitLoading = false
            })
    }

    if (formEl) {
        baTable.form.ref = formEl
        formEl.validate((valid) => {
            if (valid) {
                submitCallback()
            }
        })
    } else {
        submitCallback()
    }
    return false
}

// 打开表单后
baTable.after.toggleForm = ({ operate }) => {
    if (operate == 'Add') {
        menuRuleTreeUpdate()
    }
}

// 获取到编辑数据后
baTable.after.getEditData = () => {
    menuRuleTreeUpdate()
}

const menuRuleTreeUpdate = () => {
    getUserRules().then((res) => {
        baTable.form.extend!.menuRules = res.data.list

        if (baTable.form.items!.rules && baTable.form.items!.rules.length) {
            if (baTable.form.items!.rules.includes('*')) {
                let arr: number[] = []
                for (const key in baTable.form.extend!.menuRules) {
                    arr.push(baTable.form.extend!.menuRules[key].id)
                }
                baTable.form.extend!.defaultCheckedKeys = arr
            } else {
                baTable.form.extend!.defaultCheckedKeys = baTable.form.items!.rules
            }
        } else {
            baTable.form.extend!.defaultCheckedKeys = []
        }
        baTable.form.extend!.treeKey = uuid()
    })
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()
})
</script>

<style scoped lang="scss"></style>
