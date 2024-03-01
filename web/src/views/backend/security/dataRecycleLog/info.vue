<template>
    <el-dialog class="ba-operate-dialog" :model-value="baTable.form.operate ? true : false" @close="baTable.toggleForm">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">{{ t('Info') }}</div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'">
                <el-descriptions v-if="!isEmpty(baTable.form.extend!.info)" :column="2" border>
                    <el-descriptions-item :label="t('Id')">
                        {{ baTable.form.extend!.info.id }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.Operation administrator')">
                        {{ baTable.form.extend!.info.admin?.nickname + '(' + baTable.form.extend!.info.admin?.username + ')' }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.Recycling rule name')">
                        {{ baTable.form.extend!.info.recycle?.name }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('Connection')">
                        {{ baTable.form.extend!.info.connection }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.data sheet')">
                        {{ baTable.form.extend!.info.data_table }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.Data table primary key')">
                        {{ baTable.form.extend!.info.primary_key }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.Operator IP')">
                        {{ baTable.form.extend!.info.ip }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.dataRecycleLog.Delete time')">
                        {{ timeFormat(baTable.form.extend!.info.create_time) }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" label="User Agent">
                        {{ baTable.form.extend!.info.useragent }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" :label="t('security.dataRecycleLog.Deleted data')" label-class-name="color-red">
                        <el-tree class="table-el-tree" :data="baTable.form.extend!.info.data" :props="{ label: 'label', children: 'children' }" />
                    </el-descriptions-item>
                </el-descriptions>
            </div>
        </el-scrollbar>
        <template #footer>
            <el-button v-blur @click="onRestore(baTable.form.extend!.info.id)" type="success">
                <Icon color="#ffffff" name="el-icon-RefreshRight" />
                <span class="table-header-operate-text">{{ t('security.dataRecycleLog.restore') }}</span>
            </el-button>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type BaTable from '/@/utils/baTable'
import { timeFormat } from '/@/utils/common'
import { isEmpty } from 'lodash-es'
import { ElMessageBox } from 'element-plus'
import { restore } from '/@/api/backend/security/dataRecycleLog'

const baTable = inject('baTable') as BaTable

const { t } = useI18n()

const onRestore = (id: string) => {
    ElMessageBox.confirm(t('security.dataRecycleLog.Are you sure to restore the selected records?'), '', {
        confirmButtonText: t('security.dataRecycleLog.restore'),
        cancelButtonText: t('Cancel'),
    })
        .then(() => {
            restore([id]).then(() => {
                baTable.toggleForm()
                baTable.onTableHeaderAction('refresh', {})
            })
        })
        .catch(() => {})
}
</script>

<style scoped lang="scss">
:deep(.color-red) {
    color: var(--el-color-danger) !important;
}
.table-el-tree {
    :deep(.el-tree-node) {
        white-space: unset;
    }
    :deep(.el-tree-node__content) {
        display: block;
        align-items: unset;
        height: unset;
    }
}
</style>
