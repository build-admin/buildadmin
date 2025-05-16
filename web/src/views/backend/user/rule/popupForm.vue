<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        top="5vh"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        :destroy-on-close="true"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <FormItem
                        type="remoteSelect"
                        prop="pid"
                        :label="t('auth.rule.Superior menu rule')"
                        v-model="baTable.form.items!.pid"
                        :placeholder="t('Click select')"
                        :input-attr="{
                            params: { isTree: true },
                            field: 'title',
                            remoteUrl: baTable.api.actionUrl.get('index'),
                            emptyValues: ['', null, undefined, 0],
                            valueOnClear: 0,
                        }"
                    />
                    <el-form-item :label="t('auth.rule.Rule type')">
                        <el-radio-group v-model="baTable.form.items!.type">
                            <el-radio class="ba-el-radio" value="route" :border="true">{{ t('user.rule.Normal routing') }}</el-radio>
                            <el-radio class="ba-el-radio" value="menu_dir" :border="true">{{ t('user.rule.Member center menu contents') }}</el-radio>
                            <el-radio class="ba-el-radio" value="menu" :border="true">{{ t('user.rule.Member center menu items') }}</el-radio>
                            <el-radio class="ba-el-radio" value="nav" :border="true">{{ t('user.rule.Top bar menu items') }}</el-radio>
                            <el-radio class="ba-el-radio" value="button" :border="true">{{ t('user.rule.Page button') }}</el-radio>
                            <el-radio class="ba-el-radio" value="nav_user_menu" :border="true">{{ t('user.rule.Top bar user dropdown') }}</el-radio>
                        </el-radio-group>
                        <div class="block-help">{{ t('user.rule.Type ' + baTable.form.items!.type + ' tips') }}</div>
                    </el-form-item>
                    <el-form-item prop="title" :label="t('auth.rule.Rule title')">
                        <el-input
                            v-model="baTable.form.items!.title"
                            type="string"
                            :placeholder="t('Please input field', { field: t('auth.rule.Rule title') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="name" :label="t('auth.rule.Rule name')">
                        <el-input v-model="baTable.form.items!.name" type="string" :placeholder="t('user.rule.English name')"></el-input>
                        <div class="block-help">
                            {{ t('auth.rule.It will be registered as the web side routing name and used as the server side API authentication') }}
                        </div>
                    </el-form-item>
                    <el-form-item v-if="baTable.form.items!.type != 'button'" prop="path" :label="t('auth.rule.Routing path')">
                        <el-input v-model="baTable.form.items!.path" type="string" :placeholder="t('user.rule.Web side routing path')"></el-input>
                    </el-form-item>

                    <!-- 规则图标 -->
                    <FormItem
                        v-if="baTable.form.items!.type != 'button'"
                        type="icon"
                        :label="t('auth.rule.Rule Icon')"
                        v-model="baTable.form.items!.icon"
                        :input-attr="{ showIconName: true }"
                    />
                    <!-- 菜单类型：tab、link、iframe -->
                    <FormItem
                        v-if="!['menu_dir', 'button', 'route'].includes(baTable.form.items!.type)"
                        :label="t('auth.rule.Menu type')"
                        v-model="baTable.form.items!.menu_type"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { tab: t('auth.rule.Menu type tab'), link: t('auth.rule.Menu type link (offsite)'), iframe: 'Iframe' },
                        }"
                    />
                    <!-- URL -->
                    <el-form-item
                        prop="url"
                        v-if="!['menu_dir', 'button', 'route'].includes(baTable.form.items!.type) && baTable.form.items!.menu_type != 'tab'"
                        :label="t('auth.rule.Link address')"
                    >
                        <el-input
                            v-model="baTable.form.items!.url"
                            type="string"
                            :placeholder="t('auth.rule.Please enter the URL address of the link or iframe')"
                        ></el-input>
                    </el-form-item>
                    <!-- 组件路径 -->
                    <el-form-item
                        v-if="
                            baTable.form.items!.type == 'route' ||
                            (!['menu_dir', 'button'].includes(baTable.form.items!.type) && baTable.form.items!.menu_type == 'tab')
                        "
                        :label="t('auth.rule.Component path')"
                    >
                        <el-input
                            v-model="baTable.form.items!.component"
                            type="string"
                            :placeholder="t('user.rule.For example, if you add account/overview as a route only')"
                        ></el-input>
                        <div class="block-help component-path-tips">
                            {{ t('user.rule.Component path tips') }}
                        </div>
                    </el-form-item>
                    <!-- 扩展属性 -->
                    <el-form-item
                        v-if="!['menu_dir', 'button'].includes(baTable.form.items!.type) && baTable.form.items!.menu_type == 'tab'"
                        :label="t('auth.rule.Extended properties')"
                    >
                        <el-select
                            class="w100"
                            v-model="baTable.form.items!.extend"
                            :placeholder="t('Please select field', { field: t('auth.rule.Extended properties') })"
                        >
                            <el-option :label="t('auth.rule.none')" value="none"></el-option>
                            <el-option :label="t('auth.rule.Add as route only')" value="add_rules_only"></el-option>
                            <el-option :label="t('auth.rule.Add as menu only')" value="add_menu_only"></el-option>
                        </el-select>
                        <div class="block-help">
                            {{ t('user.rule.Web side component path, please start with /src, such as: /src/views/frontend/index') }}
                        </div>
                    </el-form-item>
                    <FormItem
                        v-if="!['menu_dir', 'menu', 'nav_user_menu'].includes(baTable.form.items!.type)"
                        :label="t('user.rule.no_login_valid')"
                        v-model="baTable.form.items!.no_login_valid"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { '0': t('user.rule.no_login_valid 0'), '1': t('user.rule.no_login_valid 1') },
                        }"
                        :block-help="t('user.rule.no_login_valid tips')"
                    />
                    <el-form-item :label="t('auth.rule.Rule comments')">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.remark"
                            type="textarea"
                            :autosize="{ minRows: 2, maxRows: 5 }"
                            :placeholder="t('Please input field', { field: t('auth.rule.Rule comments') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('auth.rule.Rule weight')">
                        <el-input
                            v-model="baTable.form.items!.weigh"
                            type="number"
                            :placeholder="t('auth.rule.Please enter the weight of menu rule (sort by)')"
                        ></el-input>
                    </el-form-item>
                    <FormItem
                        :label="t('State')"
                        v-model="baTable.form.items!.status"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { 0: t('Disable'), 1: t('Enable') },
                        }"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, inject, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [buildValidatorData({ name: 'required', title: t('auth.rule.Rule title') })],
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error(t('auth.rule.The superior menu rule cannot be the rule itself')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    name: [buildValidatorData({ name: 'required', title: t('auth.rule.Rule name') })],
    path: [buildValidatorData({ name: 'required', title: t('auth.rule.Routing path') })],
    url: [
        buildValidatorData({ name: 'required', message: t('auth.rule.Link address') }),
        buildValidatorData({ name: 'url', message: t('auth.rule.Please enter the correct URL') }),
    ],
})
</script>

<style scoped lang="scss">
.ba-el-radio {
    margin-bottom: 10px;
}
.component-path-tips {
    color: var(--el-color-warning);
}
</style>
