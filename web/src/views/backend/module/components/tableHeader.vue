<template>
    <div>
        <el-alert class="ba-table-alert" v-if="state.table.remark" :title="state.table.remark" type="info" show-icon />
        <div class="modules-header">
            <div class="table-header-buttons">
                <el-button :title="$t('refresh')" @click="onRefreshTableData" v-blur color="#40485b" type="info">
                    <Icon name="fa fa-refresh" color="#ffffff" size="14" />
                </el-button>
                <el-button-group class="ml10">
                    <el-button title="上传ZIP包安装" v-blur type="primary">
                        <Icon name="fa fa-upload" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">上传安装</span>
                    </el-button>
                    <el-button
                        @click="localModules"
                        :class="state.table.onlyLocal ? 'local-active' : ''"
                        title="已上传/安装的模块"
                        v-blur
                        type="primary"
                    >
                        <Icon name="fa fa-desktop" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">本地模块</span>
                    </el-button>
                </el-button-group>
                <el-button-group class="ml10">
                    <el-button v-blur type="success">
                        <Icon name="fa fa-cloud-upload" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">发布模块</span>
                    </el-button>
                    <el-button v-blur type="success">
                        <Icon name="fa fa-rocket" color="#ffffff" size="14" />
                        <span class="table-header-operate-text">获得积分</span>
                    </el-button>
                </el-button-group>

                <el-button v-blur class="ml10" @click="state.dialog.baAccount = true" type="success">
                    <Icon name="fa fa-user-o" color="#ffffff" size="14" />
                    <span class="table-header-operate-text">会员信息</span>
                </el-button>
            </div>
            <div class="table-search">
                <el-input
                    v-model="state.table.params.quickSearch"
                    class="xs-hidden"
                    @input="debounce(onSearchInput, 500)()"
                    placeholder="搜索其实很简单"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { state } from '../store'
import { loadData, onRefreshTableData } from '../index'
import { debounce } from '/@/utils/common'

const localModules = () => {
    state.table.onlyLocal = !state.table.onlyLocal
    loadData()
}

const onSearchInput = () => {
    state.table.modulesEbak[state.table.params.activeTab] = undefined
    loadData()
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
    background-color: var(--color-basic-white);
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
