<template>
    <el-dialog class="ba-operate-dialog" :model-value="baTable.form.operate ? true : false" @close="baTable.toggleForm">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">{{ t('Info') }}</div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'">
                <el-descriptions v-if="!isEmpty(baTable.form.extend!.info)" :column="2" border>
                    <el-descriptions-item :width="120" :span="2" :label="t('security.sensitiveDataLog.Rule name')">
                        {{ baTable.form.extend!.info.sensitive?.name }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('Id')">
                        {{ baTable.form.extend!.info.id }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Operation administrator')">
                        {{ baTable.form.extend!.info.admin?.nickname + '(' + baTable.form.extend!.info.admin?.username + ')' }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('Connection')">
                        {{ baTable.form.extend!.info.connection }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.data sheet')">
                        {{ baTable.form.extend!.info.data_table }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Modification time')">
                        {{ timeFormat(baTable.form.extend!.info.create_time) }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Operator IP')">
                        {{ baTable.form.extend!.info.ip }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Data table primary key')">
                        {{ baTable.form.extend!.info.primary_key + '=' + baTable.form.extend!.info.id_value }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Modified item')">
                        {{
                            baTable.form.extend!.info.data_field +
                            (baTable.form.extend!.info.data_comment ? '(' + baTable.form.extend!.info.data_comment + ')' : '')
                        }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.Before modification')" label-class-name="color-red">
                        <div class="info-content">{{ baTable.form.extend!.info.before }}</div>
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('security.sensitiveDataLog.After modification')" label-class-name="color-red">
                        <div class="info-content">{{ baTable.form.extend!.info.after }}</div>
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" label="User Agent">
                        {{ baTable.form.extend!.info.useragent }}
                    </el-descriptions-item>
                </el-descriptions>
                <div class="diff-box">
                    <div class="diff-box-title">{{ t('security.sensitiveDataLog.Modification comparison') }}</div>
                    <code-diff
                        diffStyle="char"
                        :old-string="baTable.form.extend!.info.before ?? ''"
                        :new-string="baTable.form.extend!.info.after ?? ''"
                    />
                </div>
            </div>
        </el-scrollbar>
        <template #footer>
            <el-button v-blur @click="onRollback(baTable.form.extend!.info.id)" type="success">
                <Icon size="16" color="#ffffff" name="fa fa-sign-in" />
                <span class="table-header-operate-text">{{ t('security.sensitiveDataLog.RollBACK') }}</span>
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
import { rollback } from '/@/api/backend/security/sensitiveDataLog'
import { CodeDiff } from 'v-code-diff'

const baTable = inject('baTable') as BaTable

const { t } = useI18n()

const onRollback = (id: string) => {
    ElMessageBox.confirm(t('security.sensitiveDataLog.Are you sure you want to rollback the record?'), '', {
        confirmButtonText: t('security.sensitiveDataLog.RollBACK'),
        cancelButtonText: t('Cancel'),
    })
        .then(() => {
            rollback([id]).then(() => {
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
.info-content {
    word-wrap: break-word;
    word-break: break-all;
}
.table-header-operate-text {
    margin-left: 6px;
}
.diff-box :deep(.d2h-file-wrapper) {
    border: 1px solid #ebeef5;
}
.diff-box-title {
    display: flex;
    font-weight: bold;
    line-height: 40px;
    align-items: center;
    justify-content: center;
}
</style>
