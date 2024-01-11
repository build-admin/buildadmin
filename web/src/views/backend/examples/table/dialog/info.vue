<!-- 示例核心代码(3/3) -->
<template>
    <el-dialog class="ba-operate-dialog" v-model="baTable.table.extend!.showInfo" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">详情</div>
        </template>
        <div v-loading="baTable.table.extend!.infoLoading" class="info-box ba-markdown">
            <div v-if="baTable.table.extend!.infoData">
                <div><code>baTable.table.extend!.showInfo == true</code>时，本弹窗就显示了，它是在按钮被点击时赋值的</div>

                <div class="mt-40">id：{{ baTable.table.extend!.infoData.id }}</div>
                <div>字符串：{{ baTable.table.extend!.infoData.string }}</div>
                <div>开关：{{ baTable.table.extend!.infoData.switch == 0 ? '关闭' : '开启' }}</div>
                <div>时间日期：{{ baTable.table.extend!.infoData.datetime }}</div>
                <div>创建时间：{{ timeFormat(baTable.table.extend!.infoData.create_time) }}</div>
            </div>
        </div>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { timeFormat } from '/@/utils/common'
import type baTableClass from '/@/utils/baTable'

const baTable = inject('baTable') as baTableClass
</script>

<style scoped lang="scss">
.info-box {
    margin-top: 60px;
    div {
        width: 100%;
        text-align: center;
    }
    .mt-40 {
        margin-top: 40px;
    }
}
</style>
