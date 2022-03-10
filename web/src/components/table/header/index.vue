<template>
    <div class="table-header">
        <el-tooltip v-if="props.buttons.includes('refresh')" content="刷新" placement="top">
            <el-button v-blur @click="onAction('refresh')" color="#40485b" class="table-header-operate" type="info">
                <Icon name="fa fa-refresh" />
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('add')" content="添加记录" placement="top">
            <el-button v-blur @click="onAction('add')" class="table-header-operate" type="primary">
                <Icon name="fa fa-plus" />
                <span class="table-header-operate-text">添加</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('edit')" content="编辑选中行" placement="top">
            <el-button v-blur @click="onAction('edit')" :disabled="!props.enableBatchOpt" class="table-header-operate" type="primary">
                <Icon name="fa fa-pencil" />
                <span class="table-header-operate-text">编辑</span>
            </el-button>
        </el-tooltip>
        <el-popconfirm
            v-if="props.buttons.includes('delete')"
            @confirm="onAction('delete')"
            confirm-button-text="删除"
            cancel-button-text="取消"
            confirmButtonType="danger"
            title="确定删除选中记录？"
        >
            <template #reference>
                <div class="mlr-12">
                    <el-tooltip content="删除选中行" placement="top">
                        <el-button v-blur :disabled="!props.enableBatchOpt" class="table-header-operate" type="danger">
                            <Icon name="fa fa-trash" />
                            <span class="table-header-operate-text">删除</span>
                        </el-button>
                    </el-tooltip>
                </div>
            </template>
        </el-popconfirm>
        <el-tooltip v-if="props.buttons.includes('unfold')" :content="(unfold ? '收缩' : '展开') + '所有子菜单'" placement="top">
            <el-button v-blur @click="changeUnfold" :type="unfold ? 'danger' : 'warning'">
                <span class="table-header-operate-text">{{ unfold ? '收缩所有' : '展开所有' }}</span>
            </el-button>
        </el-tooltip>
        <div class="table-search">
            <el-input v-model="state.quickSearch" class="xs-hidden" @input="debounce(onSearchInput, 500)()" :placeholder="quickSearchPlaceholder" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { debounce } from '/@/utils/common'
interface Props {
    buttons: HeaderOptButton[]
    enableBatchOpt: boolean
    unfold?: boolean
    quickSearchPlaceholder?: string
}
const props = withDefaults(defineProps<Props>(), {
    buttons: () => {
        return ['refresh', 'add', 'edit', 'delete']
    },
    enableBatchOpt: false,
    unfold: false,
    quickSearchPlaceholder: '搜索',
})

const state = reactive({
    quickSearch: '',
})

const emits = defineEmits<{
    (e: 'action', type: string, data: anyObj): void
}>()

const onAction = (type: string, data: anyObj = {}) => {
    emits('action', type, data)
}

const changeUnfold = () => {
    emits('action', 'unfold', { unfold: !props.unfold })
}

const onSearchInput = () => {
    emits('action', 'quick-search', { keyword: state.quickSearch })
}
</script>

<style scoped lang="scss">
.mlr-12 {
    margin-left: 12px;
}
.mlr-12 + .el-button {
    margin-left: 12px;
}
</style>
