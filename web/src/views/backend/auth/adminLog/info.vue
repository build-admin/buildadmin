<template>
    <!-- 查看详情 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
    >
        <template #title>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">查看详情</div>
        </template>
        <div
            v-loading="baTable.form.loading"
            class="ba-operate-form"
            :class="'ba-' + baTable.form.operate + '-form'"
            :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
        >
            <el-descriptions :column="2" border>
                <el-descriptions-item label="ID">
                    {{baTable.form.extend!.info.id}}
                </el-descriptions-item>
                <el-descriptions-item label="操作管理员">
                    {{baTable.form.extend!.info.username}}
                </el-descriptions-item>
                <el-descriptions-item label="标题">
                    {{baTable.form.extend!.info.title}}
                </el-descriptions-item>
                <el-descriptions-item label="操作人IP">
                    {{baTable.form.extend!.info.ip}}
                </el-descriptions-item>
                <el-descriptions-item :width="120" :span="2" label="URL">
                    {{baTable.form.extend!.info.url}}
                </el-descriptions-item>
                <el-descriptions-item :width="120" :span="2" label="User Agent">
                    {{baTable.form.extend!.info.useragent}}
                </el-descriptions-item>
                <el-descriptions-item :width="120" :span="2" label="创建时间">
                    {{ timeFormat(baTable.form.extend!.info.createtime) }}
                </el-descriptions-item>
                <el-descriptions-item :width="120" :span="2" label="请求数据">
                    <el-tree class="table-el-tree" :data="baTable.form.extend!.info.data" :props="{ label: 'label', children: 'children' }" />
                </el-descriptions-item>
            </el-descriptions>
        </div>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type BaTable from '/@/utils/baTable'
import { timeFormat } from '/@/components/table'

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
