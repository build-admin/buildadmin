<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="16">
                <el-form
                    v-if="!state.loading"
                    ref="formRef"
                    @keyup.enter="onSubmit()"
                    :model="state.form"
                    :rules="state.rules"
                    :label-position="'top'"
                    :key="state.formKey"
                >
                    <el-tabs v-model="state.activeTab" type="border-card" :before-leave="onBeforeLeave">
                        <el-tab-pane class="config-tab-pane" v-for="(group, key) in state.config" :key="key" :name="key" :label="group.title">
                            <div class="config-form-item" v-for="(item, idx) in group.list">
                                <template v-if="item.group == state.activeTab">
                                    <FormItem
                                        v-if="item.type == 'number'"
                                        :label="item.title"
                                        :type="item.type"
                                        v-model.number="state.form[item.name]"
                                        :attr="{ prop: item.name, ...item.extend }"
                                        :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                                        :data="{ tip: item.tip }"
                                        :key="'number-' + item.id"
                                    />
                                    <!-- 富文本在dialog内全屏编辑器时必须拥有很高的z-index，此处选择单独为editor设定较小的z-index -->
                                    <FormItem
                                        v-else-if="item.type == 'editor'"
                                        :label="item.title"
                                        :type="item.type"
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="onSubmit()"
                                        v-model="state.form[item.name]"
                                        :attr="{ prop: item.name, ...item.extend }"
                                        :input-attr="{
                                            placeholder: item.tip,
                                            style: {
                                                zIndex: 99,
                                            },
                                            ...item.input_extend,
                                        }"
                                        :data="{ tip: item.tip }"
                                        :key="'editor-' + item.id"
                                    />
                                    <FormItem
                                        v-else-if="item.type == 'textarea'"
                                        :label="item.title"
                                        :type="item.type"
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="onSubmit()"
                                        v-model="state.form[item.name]"
                                        :attr="{ prop: item.name, ...item.extend }"
                                        :input-attr="{ placeholder: item.tip, rows: 3, ...item.input_extend }"
                                        :data="{ tip: item.tip }"
                                        :key="'textarea-' + item.id"
                                    />
                                    <FormItem
                                        v-else
                                        :label="item.title"
                                        :type="item.type"
                                        v-model="state.form[item.name]"
                                        :attr="{ prop: item.name, ...item.extend }"
                                        :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                                        :data="{ tip: item.tip, content: item.content ? item.content : {} }"
                                        :key="'other-' + item.id"
                                    />
                                    <div class="config-form-item-name">${{ item.name }}</div>
                                    <div class="del-config-form-item">
                                        <el-popconfirm
                                            @confirm="onDelConfig(item)"
                                            v-if="item.allow_del"
                                            :confirmButtonText="t('Delete')"
                                            :title="t('routine.config.Are you sure to delete the configuration item?')"
                                        >
                                            <template #reference>
                                                <Icon class="close-icon" size="15" name="el-icon-Close" />
                                            </template>
                                        </el-popconfirm>
                                    </div>
                                </template>
                            </div>
                            <div v-if="group.name == 'mail'" class="send-test-mail">
                                <el-button @click="onTestSendMail()">{{ t('routine.config.Test mail sending') }}</el-button>
                            </div>
                            <el-button type="primary" @click="onSubmit()">{{ t('Save') }}</el-button>
                        </el-tab-pane>
                        <el-tab-pane
                            name="add_config"
                            class="config-tab-pane config-tab-pane-add"
                            :label="t('routine.config.Add configuration item')"
                        ></el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
            <el-col :xs="24" :sm="8">
                <el-card :header="t('routine.config.Quick configuration entry')">
                    <el-button v-for="item in state.quickEntrance" class="config_quick_entrance">
                        <div @click="routePush({ name: item['value'] })">{{ item['key'] }}</div>
                    </el-button>
                </el-card>
            </el-col>
        </el-row>

        <AddFrom v-if="!state.loading" v-model="state.showAddForm" :config-group="state.configGroup" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onActivated, onDeactivated, onUnmounted } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { index, postData, del, postSendTestMail } from '/@/api/backend/routine/config'
import { ElMessageBox, ElNotification } from 'element-plus'
import type { FormInstance, FormItemRule } from 'element-plus'
import { adminBaseRoutePath } from '/@/router/static/adminBase'
import AddFrom from './add.vue'
import { routePush } from '/@/utils/router'
import { buildValidatorData, type buildValidatorParams } from '/@/utils/validate'
import { useSiteConfig } from '/@/stores/siteConfig'
import type { SiteConfig } from '/@/stores/interface'
import { useI18n } from 'vue-i18n'
import { uuid } from '/@/utils/random'

defineOptions({
    name: 'routine/config',
})

const { t } = useI18n()
const siteConfig = useSiteConfig()

const formRef = ref<FormInstance>()

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
    formKey: string
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
    formKey: uuid(),
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
                            ruleArr.push(
                                buildValidatorData({ name: item as buildValidatorParams['name'], title: state.config[key].list[lKey].title })
                            )
                        })
                        rules = Object.assign(rules, {
                            [state.config[key].list[lKey].name]: ruleArr,
                        })
                    }
                    formNames[state.config[key].list[lKey].name] =
                        state.config[key].list[lKey].type == 'number'
                            ? parseFloat(state.config[key].list[lKey].value)
                            : state.config[key].list[lKey].value
                }
            }

            state.form = formNames
            state.rules = rules
            state.formKey = uuid()
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

const onSubmit = () => {
    if (!formRef.value) return
    formRef.value.validate((valid) => {
        if (valid) {
            // 只提交当前tab的表单数据
            const formData: anyObj = {}
            for (const key in state.config) {
                if (key != state.activeTab) {
                    continue
                }
                for (const lKey in state.config[key].list) {
                    formData[state.config[key].list[lKey].name] = state.form[state.config[key].list[lKey].name] ?? ''
                }
            }
            postData('edit', formData).then(() => {
                for (const key in siteConfig.$state) {
                    if (formData[key] && siteConfig.$state[key as keyof SiteConfig] != formData[key]) {
                        ;(siteConfig.$state[key as keyof SiteConfig] as any) = formData[key]
                    }
                }

                if (formData.backend_entrance != adminBaseRoutePath) {
                    window.open(window.location.href.replace(adminBaseRoutePath, formData.backend_entrance))
                    window.close()
                }
            })
        }
    })
}

const onDelConfig = (config: anyObj) => {
    del([config.id]).then(() => {
        getIndex()
    })
}

const onTestSendMail = () => {
    if (!state.form.smtp_server || !state.form.smtp_port || !state.form.smtp_user || !state.form.smtp_pass || !state.form.smtp_sender_mail) {
        ElNotification({
            type: 'error',
            message: t('routine.config.Please enter the correct mail configuration'),
        })
        return false
    }

    ElMessageBox.prompt(t('routine.config.Please enter the recipient email address'), t('routine.config.Test mail sending'), {
        confirmButtonText: t('routine.config.send out'),
        cancelButtonText: t('Cancel'),
        inputPattern: /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/,
        inputErrorMessage: t('routine.config.Please enter the correct email address'),
        beforeClose: (action, instance, done) => {
            if (action === 'confirm') {
                instance.confirmButtonLoading = true
                instance.confirmButtonText = t('routine.config.Sending')
                postSendTestMail(state.form, instance.inputValue)
                    .then((res) => {
                        done()
                    })
                    .catch((err) => {
                        done()
                    })
            } else {
                done()
            }
        },
    })
}

onMounted(() => {
    getIndex()
    if (import.meta.hot) import.meta.hot.send('custom:close-hot', { type: 'config' })
})
onActivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:close-hot', { type: 'config' })
})
onDeactivated(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'config' })
})
onUnmounted(() => {
    if (import.meta.hot) import.meta.hot.send('custom:open-hot', { type: 'config' })
})
</script>

<style scoped lang="scss">
.send-test-mail {
    padding-bottom: 20px;
}
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
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
    background-color: var(--ba-bg-color);
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
        color: var(--el-text-color-disabled);
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
            color: var(--el-text-color-disabled) !important;
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
