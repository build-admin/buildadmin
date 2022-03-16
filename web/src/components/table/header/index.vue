<template>
    <!-- 通用搜索 -->
    <ComSearch v-show="buttons.includes('comSearch') && state.showComSearch" :field="field" />

    <!-- 操作按钮组 -->
    <div v-bind="$attrs" class="table-header">
        <el-tooltip v-if="buttons.includes('refresh')" content="刷新" placement="top">
            <el-button v-blur @click="onAction('refresh', { loading: true })" color="#40485b" class="table-header-operate" type="info">
                <Icon name="fa fa-refresh" />
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="buttons.includes('add')" content="添加记录" placement="top">
            <el-button v-blur @click="onAction('add')" class="table-header-operate" type="primary">
                <Icon name="fa fa-plus" />
                <span class="table-header-operate-text">添加</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="buttons.includes('edit')" content="编辑选中行" placement="top">
            <el-button v-blur @click="onAction('edit')" :disabled="!enableBatchOpt" class="table-header-operate" type="primary">
                <Icon name="fa fa-pencil" />
                <span class="table-header-operate-text">编辑</span>
            </el-button>
        </el-tooltip>
        <el-popconfirm
            v-if="buttons.includes('delete')"
            @confirm="onAction('delete')"
            confirm-button-text="删除"
            cancel-button-text="取消"
            confirmButtonType="danger"
            title="确定删除选中记录？"
        >
            <template #reference>
                <div class="mlr-12">
                    <el-tooltip content="删除选中行" placement="top">
                        <el-button v-blur :disabled="!enableBatchOpt" class="table-header-operate" type="danger">
                            <Icon name="fa fa-trash" />
                            <span class="table-header-operate-text">删除</span>
                        </el-button>
                    </el-tooltip>
                </div>
            </template>
        </el-popconfirm>
        <el-tooltip v-if="buttons.includes('unfold')" :content="(unfold ? '收缩' : '展开') + '所有子菜单'" placement="top">
            <el-button v-blur @click="changeUnfold" :type="unfold ? 'danger' : 'warning'">
                <span class="table-header-operate-text">{{ unfold ? '收缩所有' : '展开所有' }}</span>
            </el-button>
        </el-tooltip>
        <div class="table-search">
            <el-input v-model="state.quickSearch" class="xs-hidden" @input="debounce(onSearchInput, 500)()" :placeholder="quickSearchPlaceholder" />
            <el-button-group class="table-search-button-group">
                <el-dropdown :hide-on-click="false">
                    <el-button color="#dcdfe6" plain>
                        <Icon size="14" color="#303133" name="el-icon-Grid" />
                    </el-button>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item v-for="item in field">
                                <el-checkbox
                                    v-if="item.prop"
                                    @change="onChangeShowColumn($event, item.prop!)"
                                    :checked="!item.show"
                                    :model-value="item.show"
                                    size="small"
                                    :label="item.label"
                                />
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
                <el-tooltip v-if="buttons.includes('comSearch')" :disabled="state.showComSearch" content="展开通用搜索" placement="top">
                    <el-button @click="state.showComSearch = !state.showComSearch" color="#dcdfe6" plain>
                        <Icon size="14" color="#303133" name="el-icon-Search" />
                    </el-button>
                </el-tooltip>
            </el-button-group>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { debounce } from '/@/utils/common'
import ComSearch from '/@/components/table/comSearch/index.vue'

interface Props {
    field: TableColumn[]
    buttons: HeaderOptButton[]
    enableBatchOpt: boolean
    unfold?: boolean
    quickSearchPlaceholder?: string
}
const props = withDefaults(defineProps<Props>(), {
    field: () => [],
    buttons: () => {
        return ['refresh', 'add', 'edit', 'delete']
    },
    enableBatchOpt: false,
    unfold: false,
    quickSearchPlaceholder: '搜索',
})

const state = reactive({
    quickSearch: '',
    showComSearch: false,
})

const emits = defineEmits<{
    (e: 'action', event: string, data: anyObj): void
}>()

const onAction = (event: string, data: anyObj = {}) => {
    emits('action', event, data)
}

const changeUnfold = () => {
    emits('action', 'unfold', { unfold: !props.unfold })
}

const onSearchInput = () => {
    emits('action', 'quick-search', { keyword: state.quickSearch })
}

const onChangeShowColumn = (value: boolean, field: string) => {
    emits('action', 'change-show-column', { field: field, value: value })
}
</script>

<style scoped lang="scss">
.table-header {
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 100%;
    background-color: #ffffff;
    border: 1px solid #f6f6f6;
    border-bottom: none;
    padding: 13px 15px;
    font-size: 14px;
    .table-header-operate-text {
        margin-left: 6px;
    }
    .table-header-operate .icon {
        font-size: 14px !important;
        color: #ffffff !important;
    }
}
.mlr-12 {
    margin-left: 12px;
}
.mlr-12 + .el-button {
    margin-left: 12px;
}
.table-search {
    display: flex;
    margin-left: auto;
}
.table-search-button-group {
    display: flex;
    margin-left: 12px;
    button:focus,
    button:active {
        background-color: #ffffff;
    }
    button:hover {
        background-color: #dcdfe6;
    }
}
</style>
