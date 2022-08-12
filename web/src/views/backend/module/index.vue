<template>
    <div class="default-main ba-table-box">
        <Header />
        <Tabs />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useBaAccount } from '/@/stores/baAccount'
import baUserLogin from './components/userLogin.vue'
import Header from './components/header.vue'
import Tabs from './components/tabs.vue'
import { state, loadData, loginExpired, showInfo } from './index'

const { t } = useI18n()
const baAccount = useBaAccount()

onMounted(() => {
    loadData()
})
</script>

<style scoped lang="scss">
:deep(.store-tabs) .el-tabs__content {
    padding: 10px 10px;
    min-height: 350px;
}
</style>
