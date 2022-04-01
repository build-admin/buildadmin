<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="12">
                <el-form v-if="!state.loading" ref="formRef" v-model="state.config" :label-position="'top'">
                    <el-tabs v-model="state.activeTab" type="border-card" :before-leave="onBeforeLeave">
                        <el-tab-pane class="config-tab-pane" v-for="(group, key) in state.config" :key="key" :name="key" :label="group.title">
                            <div class="config-form-item" v-for="(item, idx) in group.list">
                                <FormItem
                                    :label="item.title"
                                    :type="item.type"
                                    v-model="item.value"
                                    :inputAttr="{ placeholder: item.tip, rows: 3 }"
                                    :data="{ tip: item.tip, content: item.content ? item.content : {} }"
                                />
                                <div class="config-form-item-name">${{ item.name }}</div>
                                <div class="del-config-form-item">
                                    <el-popconfirm
                                        @confirm="onDelConfig(item)"
                                        v-if="item.allow_del"
                                        confirmButtonText="删除"
                                        title="确定删除配置项吗？"
                                    >
                                        <template #reference>
                                            <Icon class="close-icon" size="15" name="el-icon-Close" />
                                        </template>
                                    </el-popconfirm>
                                </div>
                            </div>
                            <el-button type="primary" @click="onSubmit(formRef)">提交</el-button>
                        </el-tab-pane>
                        <el-tab-pane name="add_config" class="config-tab-pane config-tab-pane-add" label="添加配置项"></el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
            <el-col :xs="24" :sm="12">
                <el-card header="快捷配置入口"> 快捷配置入口 </el-card>
            </el-col>
        </el-row>

        <AddFrom v-if="!state.loading" v-model="state.showAddForm" :config-group="state.configGroup" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { index, postData } from '/@/api/backend/routine/config'
import type { ElForm } from 'element-plus'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'
import AddFrom from './add.vue'

const formRef = ref<InstanceType<typeof ElForm>>()

const state: {
    loading: boolean
    config: anyObj
    remark: string
    configGroup: anyObj
    activeTab: string
    showAddForm: boolean
} = reactive({
    loading: true,
    config: [],
    remark: '',
    configGroup: {},
    activeTab: '',
    showAddForm: false,
})

const getIndex = () => {
    index().then((res) => {
        state.config = res.data.list
        state.remark = res.data.remark
        state.configGroup = res.data.configGroup
        state.loading = false
        for (const key in state.configGroup) {
            state.activeTab = key
            break
        }
    })
}

const onBeforeLeave = (newTabName: string | number) => {
    if (newTabName == 'add_config') {
        state.showAddForm = true
        return false
    }
}

const onSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {}

const onDelConfig = (config: anyObj) => {
    console.log(config)
}

onMounted(() => {
    getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'routine/config',
})
</script>

<style scoped lang="scss">
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--color-bg-1);
    border-bottom: none;
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-card :deep(.el-card__header) {
    height: 40px;
    padding: 0;
    line-height: 40px;
    border: none;
    padding-left: 20px;
    background-color: #f5f5f5;
}
.config-tab-pane {
    padding: 5px;
}
.config-tab-pane-add {
    width: 80%;
}
.config-form-item {
    display: flex;
    align-items: center;
    .el-form-item {
        flex: 13;
    }
    .config-form-item-name {
        opacity: 0;
        flex: 3;
        font-size: 13px;
        color: var(--color-placeholder);
        padding-left: 20px;
    }
    .del-config-form-item {
        cursor: pointer;
        flex: 1;
        padding-left: 10px;
    }
    .close-icon {
        display: none;
    }
    &:hover {
        .config-form-item-name {
            opacity: 1;
        }
        .close-icon {
            display: block;
            color: var(--color-placeholder) !important;
        }
    }
}
@media screen and (max-width: 768px) {
    .xs-mb-20 {
        margin-bottom: 20px;
    }
}
</style>
