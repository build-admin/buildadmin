<template>
    <div class="default-main ba-table-box">
        <Header />
        <Tabs />
        <baUserLogin />
        <GoodsInfo />
        <Buy />
        <Install />
        <ConfirmFileConflict />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useBaAccount } from '/@/stores/baAccount'
import baUserLogin from './components/userLogin.vue'
import Header from './components/header.vue'
import Tabs from './components/tabs.vue'
import GoodsInfo from './components/goodsInfo.vue'
import Buy from './components/buy.vue'
import Install from './components/install.vue'
import ConfirmFileConflict from './components/confirmFileConflict.vue'
import { loadData, execCommand } from './index'
import { VITE_FULL_RELOAD, DEPEND_DATA_TEMP } from './types'
import { Session } from '/@/utils/storage'

const { t } = useI18n()
const baAccount = useBaAccount()

onMounted(() => {
    loadData()
    if (import.meta.hot) {
        import.meta.hot.on('vite:beforeFullReload', () => {
            Session.set(VITE_FULL_RELOAD, true)
        })
    }

    const viteFullReload = Session.get(VITE_FULL_RELOAD)
    const dependDataTemp = Session.get(DEPEND_DATA_TEMP)
    if (viteFullReload && dependDataTemp) {
        execCommand(dependDataTemp)
    }
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
