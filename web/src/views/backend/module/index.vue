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
import { state } from './store'
import { loadData } from './index'
import TableHeader from './components/tableHeader.vue'
import BaAccount from './components/baAccount.vue'
import Tabs from './components/tabs.vue'
import GoodsInfo from './components/goodsInfo.vue'
import CommonDialog from './components/commonDialog.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

onMounted(() => {
    loadData()
    if (import.meta.hot) {
        import.meta.hot.on('vite:beforeFullReload', () => {
            if (state.common.disableHmr) throw t('module.This is a deliberate error thrown to prevent a hot update of Vite')
        })
        import.meta.hot.on('vite:beforeUpdate', () => {
            if (state.common.disableHmr) throw t('module.This is a deliberate error thrown to prevent a hot update of Vite')
        })
        import.meta.hot.on('vite:beforePrune', () => {
            if (state.common.disableHmr) throw t('module.This is a deliberate error thrown to prevent a hot update of Vite')
        })
    }
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'moduleStore/moduleStore',
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
