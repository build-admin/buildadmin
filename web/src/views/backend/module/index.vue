<template>
    <div class="default-main ba-table-box">
        <TableHeader />
        <Tabs />
        <BaAccount />
        <GoodsInfo />
        <CommonDialog />
    </div>
</template>

<script setup lang="ts">
import { onMounted, onActivated, onDeactivated, onUnmounted } from 'vue'
import { loadData } from './index'
import TableHeader from './components/tableHeader.vue'
import BaAccount from './components/baAccount.vue'
import Tabs from './components/tabs.vue'
import GoodsInfo from './components/goodsInfo.vue'
import CommonDialog from './components/commonDialog.vue'

defineOptions({
    name: 'moduleStore/moduleStore',
})

onMounted(() => {
    loadData()
    if (import.meta.hot) import.meta.hot.send('custom:close-hot', { type: 'modules' })
})
onActivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:close-hot', { type: 'modules' })
})
onDeactivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'modules' })
})
onUnmounted(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'modules' })
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
