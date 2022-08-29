<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('auth.group.GroupName') })"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <PopupForm ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { authGroup } from '/@/api/controllerUrls'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import PopupForm from './popupForm.vue'
import { defaultOptButtons } from '/@/components/table'
import { useI18n } from 'vue-i18n'
import { cloneDeep } from 'lodash'
import { ElForm } from 'element-plus'
import { getArrayKey } from '/@/utils/common'

const formRef = ref()
const tableRef = ref()
const { t } = useI18n()

const baTable = new baTableClass(
    new baTableApi(authGroup),
    {
        expandAll: true,
        dblClickNotEditColumn: [undefined],
        column: [
            { type: 'selection', align: 'center' },
            { label: t('auth.group.Group name'), prop: 'name', align: 'left' },
            { label: t('auth.group.jurisdiction'), prop: 'rules', align: 'center' },
            {
                label: t('state'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': t('Disable'), '1': t('Enable') },
            },
            { label: t('updatetime'), prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            { label: t('createtime'), prop: 'createtime', align: 'center', width: '160', render: 'datetime' },
            { label: t('operate'), align: 'center', width: '130', render: 'buttons', buttons: defaultOptButtons(['edit', 'delete']) },
        ],
    },
    {
        defaultItems: {
            status: '1',
        },
    },
    {
        // 提交前
        onSubmit: (params: { formEl: InstanceType<typeof ElForm> }) => {
            var items = cloneDeep(baTable.form.items!)

            items.rules = formRef.value.getCheckeds()

            for (const key in items) {
                if (items[key] === null) {
                    delete items[key]
                }
            }

            // 表单验证通过后执行的api请求操作
            let submitCallback = () => {
                baTable.form.submitLoading = true
                baTable.api
                    .postData(baTable.form.operate!, items)
                    .then((res) => {
                        baTable.onTableHeaderAction('refresh', {})
                        baTable.form.submitLoading = false
                        baTable.form.operateIds?.shift()
                        if (baTable.form.operateIds!.length > 0) {
                            baTable.toggleForm('edit', baTable.form.operateIds)
                        } else {
                            baTable.toggleForm()
                        }
                        baTable.runAfter('onSubmit', { res })
                    })
                    .catch((err) => {
                        baTable.form.submitLoading = false
                    })
            }

            if (params.formEl) {
                baTable.form.ref = params.formEl
                params.formEl.validate((valid) => {
                    if (valid) {
                        submitCallback()
                    }
                })
            } else {
                submitCallback()
            }
            return false
        },
        // 双击编辑前
        onTableDblclick: ({ row, column }: { row: TableRow; column: any }) => {
            return baTable.table.extend!['adminGroup'].indexOf(row.id) === -1
        },
    },
    {
        getIndex: ({ res }: { res: ApiResponse }) => {
            baTable.table.extend!['adminGroup'] = res.data.group
            let buttonsKey = getArrayKey(baTable.table.column, 'render', 'buttons')
            baTable.table.column[buttonsKey].buttons!.forEach((value, index) => {
                value.display = (row, field) => {
                    return res.data.group.indexOf(row.id) === -1
                }
            })
        },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'auth/group',
})
</script>

<style scoped lang="scss"></style>
