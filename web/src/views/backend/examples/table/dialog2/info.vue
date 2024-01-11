<!-- 示例核心代码(4/4) -->
<template>
    <el-dialog class="ba-operate-dialog" :model-value="baTable.form.operate == 'Info'" @close="baTable.form.operate = ''" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">详情</div>
        </template>
        <div class="info-box ba-markdown">
            <div v-if="baTable.form.extend!.info">
                <div><code>baTable.form.operate == 'Info'</code>时，本弹窗就显示了，它是在查看按钮被点击时赋值的</div>

                <div class="mt-40">id：{{ baTable.form.extend!.info.id }}</div>
                <div>字符串：{{ baTable.form.extend!.info.string }}</div>
                <div>开关：{{ baTable.form.extend!.info.switch == 0 ? '关闭' : '开启' }}</div>
                <div>时间日期：{{ baTable.form.extend!.info.datetime }}</div>
                <div>创建时间：{{ timeFormat(baTable.form.extend!.info.create_time) }}</div>
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
