<template>
    <div>
        <el-alert class="ba-table-alert" v-if="state.table.remark" :title="state.table.remark" type="info" show-icon />
        <div class="modules-header">
            <div class="table-header-buttons">
                <el-button :title="$t('Refresh')" @click="onRefreshTableData" v-blur color="#40485b" type="info">
                    <Icon name="fa fa-refresh" color="#ffffff" size="14" />
                </el-button>
                <el-button-group class="ml10">
                    <el-button @click="uploadInstall" :title="t('module.Upload zip package for installation')" v-blur type="primary">
                        <Icon name="fa fa-upload" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">{{ t('module.Upload installation') }}</span>
                    </el-button>
                    <el-button
                        @click="localModules"
                        :class="state.table.onlyLocal ? 'local-active' : ''"
                        :title="t('module.Uploaded / installed modules')"
                        v-blur
                        type="primary"
                    >
                        <Icon name="fa fa-desktop" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">{{ t('module.Local module') }}</span>
                    </el-button>
                </el-button-group>
                <el-button-group class="ml10">
                    <el-button @click="navigateTo('https://doc.buildadmin.com/senior/module/start.html')" v-blur type="success">
                        <Icon name="fa fa-cloud-upload" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">{{ t('module.Publishing module') }}</span>
                    </el-button>
                    <el-button @click="navigateTo('https://doc.buildadmin.com/guide/other/appendix/getPoints.html')" v-blur type="success">
                        <Icon name="fa fa-rocket" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">{{ t('module.Get points') }}</span>
                    </el-button>
                </el-button-group>

                <el-button v-blur class="ml10" @click="onShowBaAccount" type="success">
                    <Icon name="fa fa-user-o" color="#ffffff" size="14" />
                    <span class="table-header-operate-text">{{ t('module.Member information') }}</span>
                </el-button>
            </div>
            <div class="table-search">
                <el-input
                    v-model="state.table.params.quickSearch"
                    class="xs-hidden"
                    @input="debounce(onSearchInput, 500)()"
                    :placeholder="t('module.Search is actually very simple')"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { loadData, onRefreshTableData } from '../index'
import { useI18n } from 'vue-i18n'
import { debounce } from '/@/utils/common'
import { getUserInfo } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'

const { t } = useI18n()
const baAccount = useBaAccount()
const localModules = () => {
    state.table.onlyLocal = !state.table.onlyLocal
    loadData()
}

const onShowBaAccount = () => {
    state.dialog.baAccount = true
    state.loading.common = true
    getUserInfo()
        .then((res) => {
            baAccount.dataFill(res.data.userInfo)
        })
        .catch(() => {
            baAccount.removeToken()
        })
        .finally(() => {
            state.loading.common = false
        })
}

const onSearchInput = () => {
    state.table.modulesEbak[state.table.params.activeTab] = undefined
    loadData()
}

const navigateTo = (url: string) => {
    window.open(url, '_blank')
}

const uploadInstall = () => {
    state.dialog.common = true
    state.common.quickClose = true
    state.common.dialogTitle = t('module.Upload installation')
    state.common.type = 'uploadInstall'
}
</script>

<style scoped lang="scss">
.ml10 {
    margin-left: 10px;
}
.ba-table-alert {
    border: none;
}
.modules-header {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 10px;
    background-color: var(--ba-bg-color-overlay);
    border-radius: var(--el-border-radius-base);
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
.table-header-operate-text {
    padding-left: 6px;
}
.table-search {
    margin-left: auto;
}
.local-active {
    border-color: var(--el-button-active-border-color);
    background-color: var(--el-button-active-bg-color);
}
</style>
