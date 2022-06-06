<template>
    <!-- 通用搜索 -->
    <ComSearch v-show="buttons.includes('comSearch') && state.showComSearch" />

    <!-- 操作按钮组 -->
    <div v-bind="$attrs" class="table-header">
        <el-tooltip v-if="buttons.includes('refresh')" :content="t('refresh')" placement="top">
            <el-button v-blur @click="onAction('refresh', { loading: true })" color="#40485b" class="table-header-operate" type="info">
                <Icon name="fa fa-refresh" />
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="buttons.includes('add') && auth('add')" :content="t('add')" placement="top">
            <el-button v-blur @click="onAction('add')" class="table-header-operate" type="primary">
                <Icon name="fa fa-plus" />
                <span class="table-header-operate-text">{{ t('add') }}</span>
            </el-button>
        </el-tooltip>
        <el-tooltip v-if="buttons.includes('edit') && auth('edit')" :content="t('Edit selected row')" placement="top">
            <el-button v-blur @click="onAction('edit')" :disabled="!enableBatchOpt" class="table-header-operate" type="primary">
                <Icon name="fa fa-pencil" />
                <span class="table-header-operate-text">{{ t('edit') }}</span>
            </el-button>
        </el-tooltip>
        <el-popconfirm
            v-if="buttons.includes('delete') && auth('del')"
            @confirm="onAction('delete')"
            :confirm-button-text="t('delete')"
            :cancel-button-text="t('Cancel')"
            confirmButtonType="danger"
            :title="t('Are you sure to delete the selected record?')"
        >
            <template #reference>
                <div class="mlr-12">
                    <el-tooltip :content="t('Delete selected row')" placement="top">
                        <el-button v-blur :disabled="!enableBatchOpt" class="table-header-operate" type="danger">
                            <Icon name="fa fa-trash" />
                            <span class="table-header-operate-text">{{ t('delete') }}</span>
                        </el-button>
                    </el-tooltip>
                </div>
            </template>
        </el-popconfirm>
        <el-tooltip
            v-if="buttons.includes('unfold')"
            :content="(baTable.table.expandAll ? t('shrink') : t('open')) + t('All submenus')"
            placement="top"
        >
            <el-button v-blur @click="changeUnfold" class="table-header-operate" :type="baTable.table.expandAll ? 'danger' : 'warning'">
                <span class="table-header-operate-text">{{ baTable.table.expandAll ? t('Shrink all') : t('Expand all') }}</span>
            </el-button>
        </el-tooltip>

        <!-- slot -->
        <slot></slot>

        <!-- 右侧搜索框和工具按钮 -->
        <div class="table-search">
            <el-input
                v-model="state.quickSearch"
                class="xs-hidden"
                @input="debounce(onSearchInput, 500)()"
                :placeholder="quickSearchPlaceholder ? quickSearchPlaceholder : t('search')"
            />
            <el-button-group class="table-search-button-group">
                <el-dropdown :hide-on-click="false">
                    <el-button class="table-search-button-item" color="#dcdfe6" plain>
                        <Icon size="14" color="#303133" name="el-icon-Grid" />
                    </el-button>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item v-for="item in baTable.table.column">
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
                <el-tooltip
                    v-if="buttons.includes('comSearch')"
                    :disabled="state.showComSearch"
                    :content="t('Expand generic search')"
                    placement="top"
                >
                    <el-button class="table-search-button-item" @click="state.showComSearch = !state.showComSearch" color="#dcdfe6" plain>
                        <Icon size="14" color="#303133" name="el-icon-Search" />
                    </el-button>
                </el-tooltip>
            </el-button-group>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, computed, inject } from 'vue'
import { debounce, auth } from '/@/utils/common'
import type baTableClass from '/@/utils/baTable'
import ComSearch from '/@/components/table/comSearch/index.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const baTable = inject('baTable') as baTableClass

interface Props {
    buttons: HeaderOptButton[]
    quickSearchPlaceholder?: string
}
const props = withDefaults(defineProps<Props>(), {
    buttons: () => {
        return ['refresh', 'add', 'edit', 'delete']
    },
    quickSearchPlaceholder: '',
})

const state = reactive({
    quickSearch: '',
    showComSearch: false,
})

const enableBatchOpt = computed(() => (baTable.table.selection!.length > 0 ? true : false))

const emits = defineEmits<{
    (e: 'action', event: string, data: anyObj): void
}>()

const onAction = (event: string, data: anyObj = {}) => {
    emits('action', event, data)
}

const changeUnfold = () => {
    emits('action', 'unfold', { unfold: !baTable.table.expandAll })
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
    .table-search-button-item {
        margin-right: -1px;
    }
    .table-search-button-item:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
}
</style>
