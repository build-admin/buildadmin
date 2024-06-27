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
import { closeHotUpdate, openHotUpdate } from '/@/utils/vite'

defineOptions({
    name: 'moduleStore/moduleStore',
})

onMounted(() => {
    loadData()
    closeHotUpdate('modules')
})
onActivated(() => {
    closeHotUpdate('modules')
})
onDeactivated(() => {
    openHotUpdate('modules')
})
onUnmounted(() => {
    openHotUpdate('modules')
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
