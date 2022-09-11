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
import { onMounted } from 'vue'
import { loadData, execCommand, clearTempStorage, onInstall } from './index'
import { VITE_FULL_RELOAD, MODULE_TEMP } from './types'
import { Session } from '/@/utils/storage'
import TableHeader from './components/tableHeader.vue'
import BaAccount from './components/baAccount.vue'
import Tabs from './components/tabs.vue'
import GoodsInfo from './components/goodsInfo.vue'
import CommonDialog from './components/commonDialog.vue'

onMounted(() => {
    loadData()
    if (import.meta.hot) {
        import.meta.hot.on('vite:beforeFullReload', () => {
            Session.set(VITE_FULL_RELOAD, true)
        })
    }

    const moduleTemp = Session.get(MODULE_TEMP)
    if (moduleTemp) {
        const viteFullReload = Session.get(VITE_FULL_RELOAD)
        if (moduleTemp.type == 'clean-cache') {
            clearTempStorage()
        } else if (moduleTemp.type == 'install') {
            onInstall(moduleTemp.uid, moduleTemp.id)
        } else if (viteFullReload) {
            execCommand(moduleTemp)
        }
    }
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
