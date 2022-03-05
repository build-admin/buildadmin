<template>
    <div class="table-header">
        <el-tooltip v-if="props.buttons.includes('refresh')" content="刷新" placement="top">
            <el-button color="#40485b" class="table-header-operate" type="info">
                <Icon name="fa fa-refresh" />
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('add')" content="添加记录" placement="top">
            <el-button class="table-header-operate" type="primary">
                <Icon name="fa fa-plus" />
                <span class="table-header-operate-text">添加</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('edit')" content="编辑选中行" placement="top">
            <el-button :disabled="!props.enableBatchOpt" class="table-header-operate" type="primary">
                <Icon name="fa fa-pencil" />
                <span class="table-header-operate-text">编辑</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('delete')" content="删除选中行" placement="top">
            <el-button :disabled="!props.enableBatchOpt" class="table-header-operate" type="danger">
                <Icon name="fa fa-trash" />
                <span class="table-header-operate-text">删除</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="props.buttons.includes('unfold')" :content="(unfold ? '收缩' : '展开') + '所有子菜单'" placement="top">
            <el-button v-blur @click="changeUnfold" :type="unfold ? 'danger' : 'warning'">
                <span class="table-header-operate-text">{{ unfold ? '收缩所有' : '展开所有' }}</span>
            </el-button>
        </el-tooltip>
        <div class="table-search">
            <el-input class="xs-hidden" placeholder="搜索" />
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    buttons: HeaderOptButton[]
    enableBatchOpt: boolean
    unfold?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    buttons: () => {
        return ['refresh', 'add', 'edit', 'delete']
    },
    enableBatchOpt: false,
    unfold: false,
})

const emits = defineEmits<{
    (e: 'onUnfold', unfold: boolean): void
}>()

const changeUnfold = () => {
    emits('onUnfold', !props.unfold)
}
</script>

<style scoped lang="scss"></style>
