<template>
    <div class="default-main">
        <div class="ba-table-box">
            <transition name="el-fade-in">
                <div v-if="state.showComSearch" class="table-com-search">
                    <el-row>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组角色组角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">角色组</div>
                                <div class="com-search-col-input">
                                    <el-input placeholder="角色组"></el-input>
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                </div>
            </transition>
            <div class="table-header">
                <el-tooltip content="刷新" placement="top">
                    <el-button color="#9ca8b8" class="table-header-operate" type="info">
                        <Icon name="fa fa-refresh" />
                    </el-button>
                </el-tooltip>
                <el-tooltip content="编辑选中行" placement="top">
                    <el-button class="table-header-operate" type="primary">
                        <Icon name="fa fa-pencil" />
                        <span class="table-header-operate-text">编辑</span>
                    </el-button>
                </el-tooltip>
                <el-tooltip content="删除选中行" placement="top">
                    <el-button class="table-header-operate" type="danger">
                        <Icon name="fa fa-trash" />
                        <span class="table-header-operate-text">删除</span>
                    </el-button>
                </el-tooltip>
                <div class="table-search">
                    <el-input class="xs-hidden" v-model="state.searchKeyWord" placeholder="搜索" />
                    <el-button-group class="table-search-button-group">
                        <el-button color="#dcdfe6" plain>
                            <Icon size="14" color="#303133" name="el-icon-Grid" />
                        </el-button>
                        <el-button @click="state.showComSearch = !state.showComSearch" color="#dcdfe6" plain>
                            <Icon size="14" color="#303133" name="el-icon-Search" />
                        </el-button>
                    </el-button-group>
                </div>
            </div>
            <el-table header-cell-class-name="table-header-cell" :border="true" :data="tableData" stripe style="width: 100%" class="data-table">
                <el-table-column align="center" type="selection" />
                <el-table-column align="center" prop="date" label="Date" />
                <el-table-column align="center" prop="test" label="托尔斯泰" />
                <el-table-column align="center" prop="name" label="Name" />
                <el-table-column align="center" :show-overflow-tooltip="true" prop="address" label="Address" />
                <el-table-column align="center" label="操作" width="100">
                    <template #default>
                        <el-tooltip content="编辑" placement="top">
                            <el-button class="table-operate" type="primary">
                                <Icon name="fa fa-pencil" />
                            </el-button>
                        </el-tooltip>
                        <el-tooltip content="删除" placement="top">
                            <el-button class="table-operate" type="danger">
                                <Icon name="fa fa-trash" />
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div class="table-pagination">
                <el-pagination background :layout="shrink ? 'prev, next, jumper' : 'prev, pager, next, jumper'" :total="1000"></el-pagination>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { computed, reactive } from 'vue'
import { useStore } from '/@/store/index'

const store = useStore()
const state = reactive({
    showComSearch: false,
    searchKeyWord: '',
})
const shrink = computed(() => store.state.config.layout.shrink)
const tableData = [
    {
        date: '2016-05-03',
        name: 'Tom',
        test: '65945-66666666',
        address: 'No. 189, Grove St, Los Angeles',
    },
    {
        date: '2016-05-02',
        name: 'Tom',
        test: '测试',
        address: 'No. 189, Grove St, Los Angeles',
    },
    {
        date: '2016-05-04',
        name: 'Tom',
        test: '测试',
        address: 'No. 189, Grove St, Los Angeles',
    },
    {
        date: '2016-05-01',
        name: 'Tom',
        test: '测试',
        address: 'No. 189, Grove St, Los Angeles',
    },
]
</script>

<style lang="scss" scoped>
@media screen and (max-width: 768px) {
    .xs-hidden {
        display: none;
    }
}
</style>
