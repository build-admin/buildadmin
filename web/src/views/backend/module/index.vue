<template>
    <div class="default-main ba-table-box">
        <Header />
        <Tabs />
        <baUserLogin />
        <GoodsInfo />
        <Buy />
        <Install />
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
import { state, loadData, loginExpired, showInfo } from './index'

const { t } = useI18n()
const baAccount = useBaAccount()

onMounted(() => {
    loadData()
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
