<template>
    <!-- 查看详情 -->
    <el-dialog class="ba-operate-dialog" :model-value="baTable.form.operate ? true : false" @close="baTable.toggleForm">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">{{ t('Info') }}</div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'">
                <el-descriptions :column="2" border>
                    <el-descriptions-item :label="t('Id')">
                        {{ baTable.form.extend!.info.id }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('auth.adminLog.Operation administrator')">
                        {{ baTable.form.extend!.info.username }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('auth.adminLog.title')">
                        {{ baTable.form.extend!.info.title }}
                    </el-descriptions-item>
                    <el-descriptions-item :label="t('auth.adminLog.Operator IP')">
                        {{ baTable.form.extend!.info.ip }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" label="URL">
                        {{ baTable.form.extend!.info.url }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" label="User Agent">
                        {{ baTable.form.extend!.info.useragent }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" :label="t('Create time')">
                        {{ timeFormat(baTable.form.extend!.info.create_time) }}
                    </el-descriptions-item>
                    <el-descriptions-item :width="120" :span="2" :label="t('auth.adminLog.Request data')">
                        <el-tree class="table-el-tree" :data="baTable.form.extend!.info.data" :props="{ label: 'label', children: 'children' }" />
                    </el-descriptions-item>
                </el-descriptions>
            </div>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type BaTable from '/@/utils/baTable'
import { timeFormat } from '/@/utils/common'

const baTable = inject('baTable') as BaTable

const { t } = useI18n()
</script>

<style scoped lang="scss">
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
