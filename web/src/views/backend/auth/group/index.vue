<template>
    <div class="default-main ba-table-box">
        <el-alert
            class="ba-table-alert group-super-alert"
            v-if="!adminInfo.super"
            :title="t('auth.group.Manage subordinate role groups here')"
            type="info"
            show-icon
        />
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('auth.group.GroupName') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { getAdminRules } from '/@/api/backend/auth/group'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import { useAdminInfo } from '/@/stores/adminInfo'
import baTableClass from '/@/utils/baTable'
import { getArrayKey } from '/@/utils/common'
import { uuid } from '/@/utils/random'

defineOptions({
    name: 'auth/group',
})

const { t } = useI18n()
const adminInfo = useAdminInfo()
const formRef = useTemplateRef('formRef')
const tableRef = useTemplateRef('tableRef')

const baTable: baTableClass = new baTableClass(
    new baTableApi('/admin/auth.Group/'),
    {
        expandAll: true,
        dblClickNotEditColumn: [undefined],
        column: [
            { type: 'selection', align: 'center' },
            { label: t('auth.group.Group name'), prop: 'name', align: 'left', width: '200' },
            { label: t('auth.group.jurisdiction'), prop: 'rules', align: 'center' },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { 0: 'danger', 1: 'success' },
                replaceValue: { 0: t('Disable'), 1: t('Enable') },
            },
            { label: t('Update time'), prop: 'update_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Create time'), prop: 'create_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Operate'), align: 'center', width: '130', render: 'buttons', buttons: defaultOptButtons(['edit', 'delete']) },
        ],
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

// 利用双击单元格前钩子重写双击操作
baTable.before.onTableDblclick = ({ row }) => {
    return baTable.table.extend!.adminGroup.indexOf(row.id) === -1
}

// 获取到数据后钩子
baTable.after.getData = ({ res }) => {
    baTable.table.extend!.adminGroup = res.data.group
    let buttonsKey = getArrayKey(baTable.table.column, 'render', 'buttons')
    baTable.table.column[buttonsKey].buttons!.forEach((value: OptButton) => {
        value.display = (row) => {
            return res.data.group.indexOf(row.id) === -1
        }
    })
}

// 切换表单后钩子
baTable.after.toggleForm = ({ operate }) => {
    if (operate == 'Add') {
        menuRuleTreeUpdate()
    }
}

// 编辑请求完成后钩子
baTable.after.getEditData = () => {
    menuRuleTreeUpdate()
}

const menuRuleTreeUpdate = () => {
    getAdminRules().then((res) => {
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

<style scoped lang="scss">
.group-super-alert {
    margin-bottom: 10px;
}
</style>
