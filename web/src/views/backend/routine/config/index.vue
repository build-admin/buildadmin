<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="12">
                <el-form
                    v-if="!state.loading"
                    ref="formRef"
                    @keyup.enter="onSubmit(formRef)"
                    :model="state.form"
                    :rules="state.rules"
                    :label-position="'top'"
                >
                    <el-tabs v-model="state.activeTab" type="border-card" :before-leave="onBeforeLeave">
                        <el-tab-pane class="config-tab-pane" v-for="(group, key) in state.config" :key="key" :name="key" :label="group.title">
                            <div class="config-form-item" v-for="(item, idx) in group.list">
                                <FormItem
                                    v-if="item.type == 'number'"
                                    :label="item.title"
                                    :type="item.type"
                                    v-model.number="state.form[item.name]"
                                    :input-attr="{ placeholder: item.tip, rows: 3 }"
                                    :data="{ tip: item.tip, content: item.content ? item.content : {} }"
                                    :attr="Object.assign({ prop: item.name }, item.extend)"
                                />
                                <FormItem
                                    v-else
                                    :label="item.title"
                                    :type="item.type"
                                    v-model="state.form[item.name]"
                                    :input-attr="{ placeholder: item.tip, rows: 3 }"
                                    :data="{ tip: item.tip, content: item.content ? item.content : {} }"
                                    :attr="Object.assign({ prop: item.name }, item.extend)"
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
                <el-card header="快捷配置入口">
                    <el-button v-for="item in state.quickEntrance" class="config_quick_entrance">
                        <div @click="routePush('', {}, item['value'])">{{ item['key'] }}</div>
                    </el-button>
                </el-card>
            </el-col>
        </el-row>

        <AddFrom v-if="!state.loading" v-model="state.showAddForm" :config-group="state.configGroup" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { index, postData, del } from '/@/api/backend/routine/config'
import type { ElForm, FormItemRule } from 'element-plus'
import AddFrom from './add.vue'
import { routePush } from '/@/utils/router'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<InstanceType<typeof ElForm>>()

const state: {
    loading: boolean
    config: anyObj
    remark: string
    configGroup: anyObj
    activeTab: string
    showAddForm: boolean
    rules: Partial<Record<string, FormItemRule[]>>
    form: anyObj
    quickEntrance: anyObj
} = reactive({
    loading: true,
    config: [],
    remark: '',
    configGroup: {},
    activeTab: '',
    showAddForm: false,
    rules: {},
    form: {},
    quickEntrance: {},
})

const getIndex = () => {
    index()
        .then((res) => {
            state.config = res.data.list
            state.remark = res.data.remark
            state.configGroup = res.data.configGroup
            state.quickEntrance = res.data.quickEntrance
            state.loading = false
            for (const key in state.configGroup) {
                state.activeTab = key
                break
            }
            let formNames: anyObj = {}
            let rules: Partial<Record<string, FormItemRule[]>> = {}
            for (const key in state.config) {
                for (const lKey in state.config[key].list) {
                    if (state.config[key].list[lKey].rule) {
                        let ruleStr = state.config[key].list[lKey].rule.split(',')
                        let ruleArr: anyObj = []
                        ruleStr.forEach((item: string) => {
                            ruleArr.push(buildValidatorData(item, state.config[key].list[lKey].title))
                        })
                        rules = Object.assign(rules, {
                            [state.config[key].list[lKey].name]: ruleArr,
                        })
                    }
                    formNames[state.config[key].list[lKey].name] = state.config[key].list[lKey].value
                }
            }

            state.form = formNames
            state.rules = rules
        })
        .catch(() => {
            state.loading = false
        })
}

const onBeforeLeave = (newTabName: string | number) => {
    if (newTabName == 'add_config') {
        state.showAddForm = true
        return false
    }
}

const onSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            postData('edit', state.form)
        }
    })
}

const onDelConfig = (config: anyObj) => {
    del(config.id).then((res) => {
        getIndex()
    })
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
.config_quick_entrance {
    margin-left: 10px;
    margin-bottom: 10px;
}
@media screen and (max-width: 768px) {
    .xs-mb-20 {
        margin-bottom: 20px;
    }
}
</style>
