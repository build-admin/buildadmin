<template>
    <div class="default-main">
        <div class="ba-table-box">
            <div class="table-header">
                <el-tooltip content="刷新" placement="top">
                    <el-button color="#40485b" class="table-header-operate" type="info">
                        <Icon name="fa fa-refresh" />
                    </el-button>
                </el-tooltip>
                <el-tooltip content="编辑选中行" placement="top">
                    <el-button :disabled="state.tableSelection.length > 0 ? false : true" class="table-header-operate" type="primary">
                        <Icon name="fa fa-pencil" />
                        <span class="table-header-operate-text">编辑</span>
                    </el-button>
                </el-tooltip>
                <el-tooltip content="删除选中行" placement="top">
                    <el-button :disabled="state.tableSelection.length > 0 ? false : true" class="table-header-operate" type="danger">
                        <Icon name="fa fa-trash" />
                        <span class="table-header-operate-text">删除</span>
                    </el-button>
                </el-tooltip>
                <div class="table-search">
                    <el-input class="xs-hidden" v-model="state.searchKeyWord" placeholder="搜索" />
                </div>
            </div>

            <el-table
                ref="tableRef"
                class="data-table w100"
                header-cell-class-name="table-header-cell"
                :data="tableData"
                row-key="id"
                :border="true"
                stripe
                default-expand-all
                @select-all="onSelectAll"
                @select="onSelect"
                @selection-change="onSelectionChange"
                @row-dblclick="onDblclick"
            >
                <el-table-column align="center" type="selection" />
                <el-table-column align="left" prop="title" label="标题" />
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
        </div>
        <!-- 新增和编辑表单 -->
        <el-dialog
            custom-class="operate-dialog"
            :close-on-click-modal="false"
            :model-value="state.operateType == 'edit' ? true : false"
            @close="onCloseOperateDialog"
            title="编辑"
        >
            <template #title>
                <div v-drag="['.operate-dialog', '.el-dialog__header']">使用帮助</div>
            </template>
            <div>测试</div>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { nextTick, reactive, ref } from 'vue'
import type { ElTable } from 'element-plus'

const tableRef = ref<InstanceType<typeof ElTable>>()

const state = reactive({
    searchKeyWord: '',
    tableSelection: [],
    operateType: '',
})

const onDblclick = (row: any) => {
    state.operateType = 'edit'
}

const onCloseOperateDialog = () => {
    state.operateType = ''
}

const selectChildren = (children: any, type: boolean) => {
    children.map((j: any) => {
        toggleSelection(j, type)
        if (j.children) {
            selectChildren(j.children, type)
        }
    })
}

const toggleSelection = (row: any, type: boolean) => {
    if (row) {
        nextTick(() => {
            tableRef.value?.toggleRowSelection(row, type)
        })
    }
}

const isSelectAll = (selectIds: string[]) => {
    /*
     * 只检查第一个元素是否被选择
     * 全选时：selectIds为所有元素的id
     * 取消全选时：selectIds为所有子元素的id
     */
    for (const key in tableData) {
        return selectIds.includes(tableData[key].id)
    }
    return false
}

const onSelectAll = (selection: any) => {
    if (isSelectAll(selection.map((row: any) => row.id))) {
        selection.map((row: any) => {
            if (row.children) {
                selectChildren(row.children, true)
            }
        })
    } else {
        tableRef.value?.clearSelection()
    }
}

const onSelect = (selection: any, row: any) => {
    if (
        selection.some((item: any) => {
            return row.id === item.id
        })
    ) {
        if (row.children) {
            selectChildren(row.children, true)
        }
    } else {
        if (row.children) {
            selectChildren(row.children, false)
        }
    }
}

const onSelectionChange = (selection: any) => {
    state.tableSelection = selection
}
const tableData = [
    {
        id: '1',
        title: '控制台',
        name: 'Tom',
        test: '65945-66666666',
        address: 'No. 189, Grove St, Los Angeles',
    },
    {
        id: '2',
        title: '常规管理',
        name: 'Tom',
        test: '测试',
        address: 'No. 189, Grove St, Los Angeles',
        children: [
            {
                id: '22',
                title: '系统配置',
                name: 'Tom',
                test: '测试',
                address: 'No. 189, Grove St, Los Angeles',
                children: [
                    {
                        id: '31',
                        title: '系统配置32',
                        name: 'Tom',
                        test: '测试',
                        address: 'No. 189, Grove St, Los Angeles',
                    },
                ],
            },
            {
                id: '23',
                title: '个人资料',
                name: 'Tom',
                test: '测试',
                address: 'No. 189, Grove St, Los Angeles',
            },
        ],
    },
    {
        id: '3',
        title: '权限管理',
        name: 'Tom',
        test: '测试',
        address: 'No. 189, Grove St, Los Angeles',
    },
    {
        id: '4567890',
        title: '插件管理',
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
