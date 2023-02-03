<template>
    <div class="default-main">
        <div class="header-config-box">
            <el-row class="header-box">
                <div class="header">
                    <div class="header-item-box">
                        <FormItem
                            class="mr-20 table-name-item"
                            :label="t('crud.log.table_name')"
                            v-model="state.table.name"
                            type="string"
                            :placeholder="t('crud.crud.Name of the data table')"
                            :input-attr="{
                                onChange: onTableChange,
                            }"
                        />
                        <FormItem
                            class="table-comment-item"
                            :label="t('crud.crud.Data Table Notes')"
                            v-model="state.table.comment"
                            type="string"
                            :placeholder="t('crud.crud.For example: `user table` will be generated into `user management`')"
                        />
                    </div>
                    <div class="header-right">
                        <el-button type="primary" :loading="state.loading.generate" @click="onGenerate" v-blur>
                            {{ t('crud.crud.Generate CRUD code') }}
                        </el-button>
                        <el-button @click="onAbandonDesign" type="danger" v-blur>{{ t('crud.crud.give up') }}</el-button>
                    </div>
                </div>
            </el-row>
            <transition :name="state.showHeaderSeniorConfig ? 'el-zoom-in-top' : 'el-zoom-in-bottom'">
                <div v-if="state.showHeaderSeniorConfig" class="header-senior-config-box">
                    <div class="header-senior-config-form">
                        <el-form-item :label-width="140" :label="t('crud.crud.Table Quick Search Fields')">
                            <el-select :clearable="true" :multiple="true" class="w100" v-model="state.table.quickSearchField" placement="bottom">
                                <el-option
                                    v-for="(item, idx) in state.fields"
                                    :key="idx"
                                    :label="item.name + (item.title ? '-' + item.title : '')"
                                    :value="item.name"
                                />
                            </el-select>
                        </el-form-item>
                        <div class="default-sort-field-box">
                            <el-form-item :label-width="140" class="default-sort-field" :label="t('crud.crud.Table Default Sort Fields')">
                                <el-select :clearable="true" v-model="state.table.defaultSortField" placement="bottom">
                                    <el-option
                                        v-for="(item, idx) in state.fields"
                                        :key="idx"
                                        :label="item.name + (item.title ? '-' + item.title : '')"
                                        :value="item.name"
                                    />
                                </el-select>
                            </el-form-item>
                            <FormItem
                                class="default-sort-field-type"
                                :label="t('crud.crud.sort order')"
                                v-model="state.table.defaultSortType"
                                type="select"
                                :data="{
                                    content: { desc: t('crud.crud.sort order desc'), asc: t('crud.crud.sort order asc') },
                                }"
                            />
                        </div>
                        <el-form-item :label-width="140" :label="t('crud.crud.Fields as Table Columns')">
                            <el-select :clearable="true" :multiple="true" class="w100" v-model="state.table.columnFields" placement="bottom">
                                <el-option
                                    v-for="(item, idx) in state.fields"
                                    :key="idx"
                                    :label="item.name + (item.title ? '-' + item.title : '')"
                                    :value="item.name"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item :label-width="140" :label="t('crud.crud.Fields as form items')">
                            <el-select :clearable="true" :multiple="true" class="w100" v-model="state.table.formFields" placement="bottom">
                                <el-option
                                    v-for="(item, idx) in state.fields"
                                    :key="idx"
                                    :label="item.name + (item.title ? '-' + item.title : '')"
                                    :value="item.name"
                                />
                            </el-select>
                        </el-form-item>
                        <FormItem
                            :label="t('crud.crud.Generated Controller Location')"
                            v-model="state.table.controllerFile"
                            type="string"
                            :attr="{
                                'label-width': 140,
                            }"
                        />
                        <FormItem
                            :label="t('crud.crud.Generated Data Model Location')"
                            v-model="state.table.modelFile"
                            type="string"
                            :attr="{
                                'label-width': 140,
                            }"
                        />
                        <FormItem
                            :label="t('crud.crud.Generated Validator Location')"
                            v-model="state.table.validateFile"
                            type="string"
                            :attr="{
                                'label-width': 140,
                            }"
                        />
                        <FormItem
                            :label="t('crud.crud.WEB end view directory')"
                            v-model="state.table.webViewsDir"
                            type="string"
                            :attr="{
                                'label-width': 140,
                            }"
                        />
                    </div>
                </div>
            </transition>
            <div @click="state.showHeaderSeniorConfig = !state.showHeaderSeniorConfig" class="header-senior-config">
                <span>{{ t('crud.crud.Advanced Configuration') }}</span>
                <Icon
                    class="senior-config-arrow-icon"
                    size="14"
                    color="var(--el-color-info)"
                    :name="state.showHeaderSeniorConfig ? 'el-icon-ArrowUp' : 'el-icon-ArrowDown'"
                />
            </div>
        </div>
        <el-row v-loading="state.loading.init" class="fields-box" :gutter="20">
            <el-col :xs="24" :span="6">
                <el-collapse class="field-collapse" v-model="state.fieldCollapseName">
                    <el-collapse-item :title="t('crud.crud.Common Fields')" name="common">
                        <div class="field-box" :ref="tabsRefs.set">
                            <div v-for="(field, index) in fieldItem.common" :key="index" class="field-item">
                                <span>{{ field.title }}</span>
                            </div>
                        </div>
                    </el-collapse-item>
                    <el-collapse-item :title="t('crud.crud.Base Fields')" name="base">
                        <div class="field-box" :ref="tabsRefs.set">
                            <div v-for="(field, index) in fieldItem.base" :key="index" class="field-item">
                                <span>{{ field.title }}</span>
                            </div>
                        </div>
                    </el-collapse-item>
                    <el-collapse-item :title="t('crud.crud.Advanced Fields')" name="senior">
                        <div class="field-box" :ref="tabsRefs.set">
                            <div v-for="(field, index) in fieldItem.senior" :key="index" class="field-item">
                                <span>{{ field.title }}</span>
                            </div>
                        </div>
                    </el-collapse-item>
                </el-collapse>
            </el-col>
            <el-col :xs="24" :span="12">
                <div ref="designWindowRef" class="design-window ba-scroll-style" :class="state.fields.length ? '' : 'design-window-empty'">
                    <div
                        v-for="(field, index) in state.fields"
                        :key="index"
                        :class="index === state.activateField ? 'activate' : ''"
                        @click="onActivateField(index)"
                        class="design-field-box"
                        :data-id="index"
                    >
                        <div class="design-field">
                            <span>{{ t('crud.crud.Field Name') }}：</span>
                            <BaInput
                                @pointerdown.stop
                                class="design-field-name-input"
                                v-model="field.name"
                                type="string"
                                :attr="{
                                    size: 'small',
                                    onChange: onFieldNameChange,
                                }"
                            />
                        </div>
                        <div class="design-field">
                            <span>{{ t('crud.crud.field comment') }}：</span>
                            <BaInput
                                @pointerdown.stop
                                class="design-field-name-comment"
                                v-model="field.comment"
                                type="string"
                                :attr="{
                                    size: 'small',
                                }"
                            />
                        </div>
                        <div class="design-field-right">
                            <el-button
                                v-if="['remoteSelect', 'remoteSelects'].includes(field.designType)"
                                @click.stop="onEditField(index, field)"
                                type="primary"
                                size="small"
                                v-blur
                                circle
                            >
                                <Icon color="var(--el-color-white)" size="15" name="fa fa-pencil icon" />
                            </el-button>
                            <el-button @click.stop="onDelField(index)" type="danger" size="small" v-blur circle>
                                <Icon color="var(--el-color-white)" size="15" name="fa fa-trash" />
                            </el-button>
                        </div>
                    </div>
                    <div class="design-field-empty" v-if="!state.fields.length">
                        {{ t('crud.crud.Drag the left element here to start designing CRUD') }}
                    </div>
                </div>
            </el-col>
            <el-col :xs="24" :span="6">
                <div class="field-config ba-scroll-style">
                    <div v-if="state.activateField === -1" class="design-field-empty">
                        {{ t('crud.crud.Please select a field from the left first') }}
                    </div>
                    <div v-else :key="'activate-field-' + state.activateField">
                        <el-form label-position="top">
                            <el-divider content-position="left">{{ t('crud.crud.Common') }}</el-divider>
                            <el-form-item :label="t('crud.crud.generate')">
                                <el-select
                                    @change="onFieldDesignTypeChange"
                                    class="w100"
                                    v-model="state.fields[state.activateField].designType"
                                    placement="bottom"
                                >
                                    <el-option v-for="(item, idx) in designTypes" :key="idx" :label="item.name" :value="idx" />
                                </el-select>
                            </el-form-item>
                            <FormItem
                                :label="t('crud.crud.Field comments (CRUD dictionary)')"
                                type="textarea"
                                :input-attr="{
                                    rows: 2,
                                }"
                                :placeholder="
                                    t(
                                        'crud.crud.The field comment will be used as the CRUD dictionary, and will be identified as the field title before the colon, and as the data dictionary after the colon'
                                    )
                                "
                                v-model="state.fields[state.activateField].comment"
                            />
                            <el-divider content-position="left">{{ t('crud.crud.Field Properties') }}</el-divider>
                            <FormItem
                                :label="t('crud.crud.Field Name')"
                                type="string"
                                v-model="state.fields[state.activateField].name"
                                :input-attr="{
                                    onChange: onFieldNameChange,
                                }"
                            />
                            <template v-if="state.fields[state.activateField].dataType">
                                <FormItem :label="t('crud.crud.Field Type')" type="textarea" v-model="state.fields[state.activateField].dataType" />
                            </template>
                            <template v-else>
                                <FormItem :label="t('crud.crud.Field Type')" type="string" v-model="state.fields[state.activateField].type" />
                                <div class="field-inline">
                                    <FormItem
                                        :label="t('crud.crud.length')"
                                        type="number"
                                        v-model.number="state.fields[state.activateField].length"
                                    />
                                    <FormItem
                                        :label="t('crud.crud.decimal point')"
                                        type="number"
                                        v-model.number="state.fields[state.activateField].precision"
                                    />
                                </div>
                            </template>
                            <FormItem
                                :label="t('crud.crud.Field Defaults')"
                                :placeholder="t('crud.crud.You can directly enter null, 0, empty string')"
                                type="string"
                                v-model="state.fields[state.activateField].default"
                            />
                            <div class="field-inline">
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.state.Primary key')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].primaryKey"
                                />
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Auto increment')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].autoIncrement"
                                />
                            </div>
                            <div class="field-inline">
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Unsigned')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].unsigned"
                                />
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Allow NULL')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].null"
                                />
                            </div>
                            <template v-if="!isEmpty(state.fields[state.activateField].table)">
                                <el-divider content-position="left">{{ t('crud.crud.Field Table Properties') }}</el-divider>
                                <template v-for="(item, idx) in state.fields[state.activateField].table" :key="idx">
                                    <FormItem
                                        :label="$t('crud.crud.' + idx)"
                                        :type="item.type"
                                        v-model="state.fields[state.activateField].table[idx].value"
                                        :placeholder="state.fields[state.activateField].table[idx].placeholder ?? ''"
                                        :data="{
                                            content: state.fields[state.activateField].table[idx].options ?? {},
                                        }"
                                        :input-attr="state.fields[state.activateField].table[idx].attr ?? {}"
                                    />
                                </template>
                            </template>
                            <template v-if="!isEmpty(state.fields[state.activateField].form)">
                                <el-divider content-position="left">{{ t('crud.crud.Field Form Properties') }}</el-divider>
                                <template v-for="(item, idx) in state.fields[state.activateField].form" :key="idx">
                                    <FormItem
                                        :label="$t('crud.crud.' + idx)"
                                        :type="item.type"
                                        v-model="state.fields[state.activateField].form[idx].value"
                                        :placeholder="state.fields[state.activateField].form[idx].placeholder ?? ''"
                                        :data="{
                                            content: state.fields[state.activateField].form[idx].options ?? {},
                                        }"
                                        :input-attr="state.fields[state.activateField].form[idx].attr ?? {}"
                                    />
                                </template>
                            </template>
                        </el-form>
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-dialog
            @close="onCancelRemoteSelect"
            class="ba-operate-dialog"
            :model-value="state.remoteSelectPre.show"
            :title="t('crud.crud.Remote drop-down association information')"
            :close-on-click-modal="false"
            @keyup.enter="onSaveRemoteSelect"
        >
            <div class="ba-operate-form" :style="'width: calc(100% - 80px)'">
                <el-form
                    ref="formRef"
                    :model="state.remoteSelectPre.form"
                    :rules="remoteSelectPreFormRules"
                    v-loading="state.remoteSelectPre.loading"
                    label-position="right"
                    label-width="160px"
                    :destroy-on-close="true"
                >
                    <FormItem
                        prop="table"
                        type="select"
                        :label="t('crud.crud.Associated Data Table')"
                        v-model="state.remoteSelectPre.form.table"
                        :key="JSON.stringify(state.remoteSelectPre.dbList)"
                        :data="{
                            content: state.remoteSelectPre.dbList,
                        }"
                        :input-attr="{ onChange: onJoinTableChange }"
                    />
                    <div v-loading="state.loading.remoteSelect">
                        <FormItem
                            prop="pk"
                            type="select"
                            :label="t('crud.crud.Drop down value field')"
                            v-model="state.remoteSelectPre.form.pk"
                            :placeholder="t('crud.crud.Please select the value field of the select component')"
                            :key="'select-value' + JSON.stringify(state.remoteSelectPre.fieldList)"
                            :data="{
                                content: state.remoteSelectPre.fieldList,
                            }"
                        />
                        <FormItem
                            prop="label"
                            type="select"
                            :label="t('crud.crud.Drop down label field')"
                            v-model="state.remoteSelectPre.form.label"
                            :placeholder="t('crud.crud.Please select the label field of the select component')"
                            :key="'select-label' + JSON.stringify(state.remoteSelectPre.fieldList)"
                            :data="{
                                content: state.remoteSelectPre.fieldList,
                            }"
                        />
                        <FormItem
                            prop="joinField"
                            type="selects"
                            :label="t('crud.crud.Fields displayed in the table')"
                            v-model="state.remoteSelectPre.form.joinField"
                            :placeholder="t('crud.crud.Please select the fields displayed in the table')"
                            :key="'join-field' + JSON.stringify(state.remoteSelectPre.fieldList)"
                            :data="{
                                content: state.remoteSelectPre.fieldList,
                            }"
                        />
                        <FormItem
                            prop="controllerFile"
                            type="select"
                            :label="t('crud.crud.Controller position')"
                            v-model="state.remoteSelectPre.form.controllerFile"
                            :placeholder="t('crud.crud.Please select the controller of the data table')"
                            :key="'controller-file' + JSON.stringify(state.remoteSelectPre.controllerFileList)"
                            :data="{
                                content: state.remoteSelectPre.controllerFileList,
                            }"
                            :attr="{
                                'block-help': t(
                                    'crud.crud.The remote pull-down will request the corresponding controller to obtain data, so it is recommended that you create the CRUD of the associated table'
                                ),
                            }"
                        />
                        <FormItem
                            type="select"
                            :label="t('crud.crud.Data Model Location')"
                            v-model="state.remoteSelectPre.form.modelFile"
                            :placeholder="t('crud.crud.Please select the data model location of the data table')"
                            :key="'model-file' + JSON.stringify(state.remoteSelectPre.modelFileList)"
                            :data="{
                                content: state.remoteSelectPre.modelFileList,
                            }"
                            :attr="{
                                'block-help': t(
                                    'crud.crud.If it is left blank, the model of the associated table will be generated automatically If the table already has a model, it is recommended to select it to avoid repeated generation'
                                ),
                            }"
                        />
                    </div>
                </el-form>
            </div>
            <template #footer>
                <div :style="'width: calc(100% - 88px)'">
                    <el-button @click="onCancelRemoteSelect">{{ $t('Cancel') }}</el-button>
                    <el-button v-blur @click="onSaveRemoteSelect" type="primary">
                        {{ $t('Save') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
        <el-dialog
            @close="closeConfirmGenerate"
            class="ba-operate-dialog confirm-generate-dialog"
            :model-value="state.confirmGenerate.show"
            :title="t('crud.crud.Confirm CRUD code generation')"
        >
            <div class="confirm-generate-dialog-body">
                <el-alert
                    v-if="state.confirmGenerate.controller"
                    :title="t('crud.crud.The controller already exists Continuing to generate will automatically overwrite the existing code!')"
                    center
                    type="error"
                />
                <br />
                <el-alert
                    v-if="state.confirmGenerate.table"
                    :title="
                        t(
                            'crud.crud.The data table already exists Continuing to generate will automatically delete the original table and create a new one!'
                        )
                    "
                    center
                    type="error"
                />
            </div>
            <template #footer>
                <div class="confirm-generate-dialog-footer">
                    <el-button @click="closeConfirmGenerate">{{ $t('Cancel') }}</el-button>
                    <el-button :loading="state.loading.generate" v-blur @click="startGenerate" type="primary">
                        {{ t('crud.crud.Continue building') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, nextTick } from 'vue'
import BaInput from '/@/components/baInput/index.vue'
import FormItem from '/@/components/formItem/index.vue'
import { fieldItem, designTypes, tableFieldsKey } from '/@/views/backend/crud/index'
import type { FieldItem } from '/@/views/backend/crud/index'
import { cloneDeep, range, isEmpty } from 'lodash-es'
import Sortable, { SortableEvent } from 'sortablejs'
import { useTemplateRefsList } from '@vueuse/core'
import { changeStep, state as crudState, getTableAttr } from '/@/views/backend/crud/index'
import { ElNotification, FormItemRule, FormInstance, ElMessageBox } from 'element-plus'
import { getDatabaseList, getFileData, generateCheck, generate, parseFieldData, postLogStart } from '/@/api/backend/crud'
import { getTableFieldList } from '/@/api/common'
import { buildValidatorData, regularVarName } from '/@/utils/validate'
import { getArrayKey } from '/@/utils/common'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const designWindowRef = ref()
const formRef = ref<FormInstance>()
const tabsRefs = useTemplateRefsList<HTMLElement>()
let nameRepeatCount = 1
const state: {
    loading: {
        init: boolean
        generate: boolean
        remoteSelect: boolean
    }
    table: {
        name: string
        comment: string
        quickSearchField: string[]
        defaultSortField: string
        formFields: string[]
        columnFields: string[]
        defaultSortType: string
        modelFile: string
        controllerFile: string
        validateFile: string
        webViewsDir: string
    }
    fields: FieldItem[]
    activateField: number
    fieldCollapseName: string[]
    remoteSelectPre: {
        show: boolean
        index: number
        dbList: anyObj
        fieldList: anyObj
        modelFileList: anyObj
        controllerFileList: anyObj
        loading: boolean
        hideDelField: boolean
        form: {
            table: string
            pk: string
            label: string
            joinField: string[]
            modelFile: string
            controllerFile: string
        }
    }
    showHeaderSeniorConfig: boolean
    confirmGenerate: {
        show: boolean
        table: boolean
        controller: boolean
    }
} = reactive({
    loading: {
        init: false,
        generate: false,
        remoteSelect: false,
    },
    table: {
        name: '',
        comment: '',
        quickSearchField: [],
        defaultSortField: '',
        formFields: [],
        columnFields: [],
        defaultSortType: 'desc',
        modelFile: '',
        controllerFile: '',
        validateFile: '',
        webViewsDir: '',
    },
    fields: [],
    activateField: -1,
    fieldCollapseName: ['common', 'base', 'senior'],
    remoteSelectPre: {
        show: false,
        index: -1,
        dbList: [],
        fieldList: [],
        modelFileList: [],
        controllerFileList: [],
        loading: false,
        hideDelField: false,
        form: {
            table: '',
            pk: '',
            label: '',
            joinField: [],
            modelFile: '',
            controllerFile: '',
        },
    },
    showHeaderSeniorConfig: false,
    confirmGenerate: {
        show: false,
        table: false,
        controller: false,
    },
})

type TableKey = keyof typeof state.table

const onActivateField = (idx: number) => {
    state.activateField = idx
}

const onFieldDesignTypeChange = () => {
    const field = cloneDeep(state.fields[state.activateField])
    for (const tKey in field.table) {
        field.table[tKey] = field.table[tKey].value
    }
    for (const tKey in field.form) {
        field.form[tKey] = field.form[tKey].value
    }
    state.fields[state.activateField] = handleFieldAttr(field)
}

const onFieldNameChange = (val: string) => {
    for (const key in tableFieldsKey) {
        for (const idx in state.table[tableFieldsKey[key] as TableKey] as string[]) {
            if (!getArrayKey(state.fields, 'name', state.table[tableFieldsKey[key] as TableKey][idx])) {
                ;(state.table[tableFieldsKey[key] as TableKey] as string[])[idx] = val
            }
        }
    }
    if (state.table.defaultSortField) {
        if (!getArrayKey(state.fields, 'name', state.table.defaultSortField)) {
            state.table.defaultSortField = val
        }
    }
}

const onDelField = (index: number) => {
    if (!state.fields[index]) return
    state.activateField = -1
    if (state.fields[index].name == state.table.defaultSortField) {
        state.table.defaultSortField = ''
    }

    for (const key in tableFieldsKey) {
        const delIdx = (state.table[tableFieldsKey[key] as TableKey] as string[]).findIndex((item) => {
            return item == state.fields[index].name
        })
        if (delIdx != -1) {
            ;(state.table[tableFieldsKey[key] as TableKey] as string[]).splice(delIdx, 1)
        }
    }

    state.fields.splice(index, 1)
}

const showRemoteSelectPre = (index: number, hideDelField = false) => {
    state.remoteSelectPre.show = true
    state.remoteSelectPre.loading = true
    state.remoteSelectPre.index = index
    state.remoteSelectPre.hideDelField = hideDelField
    getDatabaseList()
        .then((res) => {
            state.remoteSelectPre.dbList = res.data.dbs
            if (state.fields[index] && state.fields[index].form['remote-table'].value) {
                state.remoteSelectPre.form.table = state.fields[index].form['remote-table'].value
                state.remoteSelectPre.form.pk = state.fields[index].form['remote-pk'].value
                state.remoteSelectPre.form.label = state.fields[index].form['remote-field'].value
                state.remoteSelectPre.form.controllerFile = state.fields[index].form['remote-controller'].value
                state.remoteSelectPre.form.modelFile = state.fields[index].form['remote-model'].value
                state.remoteSelectPre.form.joinField = state.fields[index].form['relation-fields'].value.split(',')
                if (isEmpty(state.remoteSelectPre.fieldList)) {
                    getTableFieldList(state.fields[index].form['remote-table'].value).then((res) => {
                        const fieldSelect: anyObj = {}
                        for (const key in res.data.fieldList) {
                            fieldSelect[key] = (key ? key + ' - ' : '') + res.data.fieldList[key]
                        }
                        state.remoteSelectPre.fieldList = fieldSelect
                    })
                }
                if (isEmpty(state.remoteSelectPre.modelFileList) || isEmpty(state.remoteSelectPre.controllerFileList)) {
                    getFileData(state.fields[index].form['remote-table'].value).then((res) => {
                        state.remoteSelectPre.modelFileList = res.data.modelFileList
                        state.remoteSelectPre.controllerFileList = res.data.controllerFileList
                    })
                }
            }
        })
        .finally(() => {
            state.remoteSelectPre.loading = false
        })
}

const onEditField = (index: number, field: FieldItem) => {
    if (['remoteSelect', 'remoteSelects'].includes(field.designType)) return showRemoteSelectPre(index)
}

const closeConfirmGenerate = () => {
    state.confirmGenerate.show = false
}

const startGenerate = () => {
    state.loading.generate = true
    const fields = cloneDeep(state.fields)
    for (const key in fields) {
        for (const tKey in fields[key].table) {
            fields[key].table[tKey] = fields[key].table[tKey].value
        }
        for (const tKey in fields[key].form) {
            fields[key].form[tKey] = fields[key].form[tKey].value
        }
    }
    generate({
        table: state.table,
        fields: fields,
    })
        .then(() => {
            router.go(0)
        })
        .finally(() => {
            state.loading.generate = false
            closeConfirmGenerate()
        })
}

const onGenerate = () => {
    let msg = ''

    // 字段名检查
    state.fields.find((item) => {
        if (!regularVarName(item.name)) {
            msg = t(
                'crud.crud.Field name is invalid It starts with a letter or underscore and cannot contain any character other than letters, digits, or underscores',
                { field: item.name }
            )
            return true
        }
    })

    // 主键检查
    const pkIndex = state.fields.findIndex((item) => {
        return item.primaryKey
    })
    if (pkIndex === -1) {
        msg = t('crud.crud.Please design the primary key field!')
    }

    // 表名检查
    if (!state.table.name) msg = t('crud.crud.Please enter the data table name!')

    if (msg) {
        ElNotification({
            type: 'error',
            message: msg,
        })
        return
    }

    state.loading.generate = true
    generateCheck({
        table: state.table.name,
        controllerFile: state.table.controllerFile,
    })
        .then(() => {
            startGenerate()
        })
        .catch((res) => {
            state.loading.generate = false
            if (res.code == -1) {
                state.confirmGenerate.show = true
                state.confirmGenerate.table = res.data.table
                state.confirmGenerate.controller = res.data.controller
            } else {
                ElNotification({
                    type: 'error',
                    message: res.msg,
                })
            }
        })
}

const onAbandonDesign = () => {
    if (!state.table.name && !state.table.comment && !state.fields.length) {
        return changeStep('start')
    }
    ElMessageBox.confirm(t('crud.crud.It is irreversible to give up the design Are you sure you want to give up?'), t('Reminder'), {
        confirmButtonText: t('crud.crud.give up'),
        cancelButtonText: t('Cancel'),
        type: 'warning',
    })
        .then(() => {
            changeStep('start')
        })
        .catch(() => {})
}

interface SortableEvt extends SortableEvent {
    originalEvent?: DragEvent
}

/**
 * 处理字段的属性
 */
const handleFieldAttr = (field: FieldItem) => {
    const designTypeAttr = cloneDeep(designTypes[field.designType])
    for (const tKey in field.form) {
        if (designTypeAttr.form[tKey]) designTypeAttr.form[tKey].value = field.form[tKey]
        if (tKey == 'image-multi' && field.form[tKey]) {
            designTypeAttr.table['render'] = getTableAttr('render', 'images')
        }
    }
    for (const tKey in field.table) {
        if (designTypeAttr.table[tKey]) designTypeAttr.table[tKey].value = field.table[tKey]
    }
    field.form = designTypeAttr.form
    field.table = designTypeAttr.table
    return field
}

const loadData = () => {
    if (!['db', 'sql', 'log'].includes(crudState.type)) return

    state.loading.init = true

    // 从历史记录开始
    if (crudState.type == 'log') {
        postLogStart(parseInt(crudState.startData.logId))
            .then((res) => {
                state.table = res.data.table
                const fields = res.data.fields
                for (const key in fields) {
                    const field = handleFieldAttr(cloneDeep(fields[key]))
                    state.fields.push(field)
                }
            })
            .finally(() => {
                state.loading.init = false
            })
        return
    }

    // 从数据表或sql开始
    parseFieldData(crudState.type, crudState.startData.db, crudState.startData.sql)
        .then((res) => {
            let fields = []
            for (const key in res.data.columns) {
                const field = handleFieldAttr(res.data.columns[key])
                if (!['id', 'update_time', 'create_time', 'updatetime', 'createtime'].includes(field.name)) {
                    state.table.formFields.push(field.name)
                }
                if (!['textarea', 'file', 'files', 'editor', 'password', 'array'].includes(field.designType)) {
                    state.table.columnFields.push(field.name)
                }
                if (field.designType == 'pk') {
                    state.table.defaultSortField = field.name
                    state.table.quickSearchField.push(field.name)
                }
                if (field.designType == 'weigh') {
                    state.table.defaultSortField = field.name
                }
                fields.push(field)
            }
            state.fields = fields
            state.table.comment = res.data.comment
            if (crudState.type == 'db' && crudState.startData.db) {
                state.table.name = crudState.startData.db
                onTableChange(crudState.startData.db)
            }
        })
        .finally(() => {
            state.loading.init = false
        })
}

onMounted(() => {
    loadData()
    const sortable = Sortable.create(designWindowRef.value, {
        group: 'design-field',
        animation: 200,
        filter: '.design-field-empty',
        onAdd: (evt: SortableEvt) => {
            const name = evt.originalEvent?.dataTransfer?.getData('name')
            const field = fieldItem[name as keyof typeof fieldItem]
            if (field && field[evt.oldIndex!]) {
                const data = handleFieldAttr(cloneDeep(field[evt.oldIndex!]))

                // 主键重复检测
                if (data.primaryKey == true) {
                    const primaryKeyField = state.fields.find((item) => {
                        return item.primaryKey
                    })
                    if (primaryKeyField) {
                        ElNotification({
                            type: 'error',
                            message: t('crud.crud.There can only be one primary key field'),
                        })
                        return evt.item.remove()
                    }
                    state.table.defaultSortField = data.name
                    state.table.quickSearchField.push(data.name)
                }

                // 出现权重字段则以其排序
                if (data.designType == 'weigh') {
                    state.table.defaultSortField = data.name
                }

                // name 重复字段自动重命名
                const nameRepeatKey = getArrayKey(state.fields, 'name', data.name)
                if (nameRepeatKey) {
                    data.name = data.name + nameRepeatCount
                    nameRepeatCount++
                }

                state.fields.splice(evt.newIndex!, 0, data)

                // 远程下拉参数预填
                if (['remoteSelect', 'remoteSelects'].includes(data.designType)) {
                    showRemoteSelectPre(evt.newIndex!, true)
                }

                // 表单表格字段预定义
                if (!data.formBuildExclude) {
                    state.table.formFields.push(data.name)
                }
                if (!data.tableBuildExclude) {
                    state.table.columnFields.push(data.name)
                }
            }
            evt.item.remove()
            nextTick(() => {
                sortable.sort(range(state.fields.length).map((value) => value.toString()))
            })
        },
        onEnd: (evt) => {
            const temp = state.fields[evt.oldIndex!]
            state.fields.splice(evt.oldIndex!, 1)
            state.fields.splice(evt.newIndex!, 0, temp)
            nextTick(() => {
                sortable.sort(range(state.fields.length).map((value) => value.toString()))
            })
        },
    })

    tabsRefs.value.forEach((item, index) => {
        Sortable.create(item, {
            sort: false,
            group: {
                name: 'design-field',
                pull: 'clone',
                put: false,
            },
            animation: 200,
            setData: (dataTransfer) => {
                dataTransfer.setData('name', Object.keys(fieldItem)[index])
            },
        })
    })
})

const onTableChange = (val: string) => {
    if (!val) return
    getFileData(val).then((res) => {
        state.table.modelFile = res.data.modelFile
        state.table.controllerFile = res.data.controllerFile
        state.table.validateFile = res.data.validateFile
        state.table.webViewsDir = res.data.webViewsDir
    })
}

const onJoinTableChange = (val: string) => {
    if (!val) return
    resetRemoteSelectForm()
    state.remoteSelectPre.form.table = val
    state.loading.remoteSelect = true
    getTableFieldList(val)
        .then((res) => {
            state.remoteSelectPre.form.pk = res.data.pk

            const preLabel = ['name', 'title', 'username', 'nickname']
            for (const key in res.data.fieldList) {
                if (preLabel.includes(key)) {
                    state.remoteSelectPre.form.label = key
                    state.remoteSelectPre.form.joinField.push(key)
                    break
                }
            }

            const fieldSelect: anyObj = {}
            for (const key in res.data.fieldList) {
                fieldSelect[key] = (key ? key + ' - ' : '') + res.data.fieldList[key]
            }
            state.remoteSelectPre.fieldList = fieldSelect
        })
        .finally(() => {
            state.loading.remoteSelect = false
        })

    getFileData(val).then((res) => {
        state.remoteSelectPre.modelFileList = res.data.modelFileList
        state.remoteSelectPre.controllerFileList = res.data.controllerFileList

        if (Object.keys(res.data.modelFileList).includes(res.data.modelFile)) {
            state.remoteSelectPre.form.modelFile = res.data.modelFile
        }
        if (Object.keys(res.data.controllerFileList).includes(res.data.controllerFile)) {
            state.remoteSelectPre.form.controllerFile = res.data.controllerFile
        }
    })
}

const onSaveRemoteSelect = () => {
    const submitCallback = () => {
        state.fields[state.remoteSelectPre.index].form['remote-table'].value = state.remoteSelectPre.form.table
        state.fields[state.remoteSelectPre.index].form['remote-pk'].value = state.remoteSelectPre.form.pk
        state.fields[state.remoteSelectPre.index].form['remote-field'].value = state.remoteSelectPre.form.label
        state.fields[state.remoteSelectPre.index].form['remote-controller'].value = state.remoteSelectPre.form.controllerFile
        state.fields[state.remoteSelectPre.index].form['remote-model'].value = state.remoteSelectPre.form.modelFile
        state.fields[state.remoteSelectPre.index].form['relation-fields'].value = state.remoteSelectPre.form.joinField.join(',')
        state.remoteSelectPre.index = -1
        state.remoteSelectPre.show = false
        resetRemoteSelectForm()
    }

    if (formRef.value) {
        formRef.value.validate((valid) => {
            if (valid) {
                submitCallback()
            }
        })
    }
}

const onCancelRemoteSelect = () => {
    state.remoteSelectPre.show = false
    resetRemoteSelectForm()
    if (state.remoteSelectPre.index !== -1 && state.remoteSelectPre.hideDelField) {
        onDelField(state.remoteSelectPre.index)
    }
}

const resetRemoteSelectForm = () => {
    for (const key in state.remoteSelectPre.form) {
        if (key == 'joinField') {
            state.remoteSelectPre.form[key] = []
        } else {
            ;(state.remoteSelectPre.form[key as keyof typeof state.remoteSelectPre.form] as string) = ''
        }
    }
}

const remoteSelectPreFormRules: Partial<Record<string, FormItemRule[]>> = reactive({
    table: [buildValidatorData({ name: 'required', title: t('crud.crud.remote-table') })],
    pk: [buildValidatorData({ name: 'required', title: t('crud.crud.Drop down value field') })],
    label: [buildValidatorData({ name: 'required', title: t('crud.crud.Drop down label field') })],
    joinField: [buildValidatorData({ name: 'required', title: t('crud.crud.Fields displayed in the table') })],
    controllerFile: [buildValidatorData({ name: 'required', title: t('crud.crud.Controller position') })],
})
</script>

<style scoped lang="scss">
.form-item-position-right {
    display: flex !important;
    align-items: center;
    :deep(.el-form-item__label) {
        margin-bottom: 0 !important;
    }
}
.default-main {
    margin-bottom: 0;
}
.mr-20 {
    margin-right: 20px;
}
.field-collapse :deep(.el-collapse-item__header) {
    padding-left: 10px;
    user-select: none;
}
.field-box {
    padding: 10px;
}
.field-item {
    display: inline-block;
    padding: 4px 18px;
    border: 1px dashed var(--el-border-color);
    border-radius: var(--el-border-radius-base);
    margin: 6px;
    cursor: pointer;
    user-select: none;
    &:hover {
        border-color: var(--el-color-primary);
    }
}
.header-config-box {
    position: relative;
    .header-senior-config {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        height: 24px;
        bottom: -24px;
        padding: 4px 20px;
        padding-top: 0;
        left: calc(50% - 10px);
        font-size: var(--el-font-size-small);
        border-bottom-left-radius: 50px;
        border-bottom-right-radius: 50px;
        background-color: var(--ba-bg-color-overlay);
        color: var(--el-color-info);
        cursor: pointer;
        user-select: none;
        .senior-config-arrow-icon {
            margin-left: 4px;
        }
    }
}
.header-senior-config-box {
    width: 100%;
    padding: 10px;
    background-color: var(--ba-bg-color-overlay);
}
.header-senior-config-form {
    width: 50%;
    :deep(.el-form-item__label) {
        justify-content: flex-start;
    }
}
.header-box {
    display: flex;
    align-items: center;
    height: 60px;
    padding: 10px;
    background-color: var(--ba-bg-color-overlay);
    border-radius: var(--el-border-radius-base);
    .header,
    .header-item-box {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        :deep(.el-form-item) {
            margin-bottom: 0;
        }
    }
    .header-item-box {
        width: 50%;
    }
    .table-name-item {
        flex: 3;
    }
    .table-comment-item {
        flex: 4;
    }
    .header-right {
        margin-left: auto;
    }
}
.default-sort-field-box {
    display: flex;
    .default-sort-field {
        flex: 6;
    }
    .default-sort-field-type {
        flex: 3;
    }
}
.fields-box {
    margin-top: 36px;
}
.design-window.design-window-empty {
    border: 1px dashed var(--el-border-color);
}
.design-field-empty {
    display: flex;
    height: 100%;
    color: var(--el-color-info);
    font-size: var(--el-font-size-medium);
    align-items: center;
    justify-content: center;
}
.design-window {
    overflow-x: auto;
    height: calc(100vh - 200px);
    border-radius: var(--el-border-radius-base);
    background-color: var(--ba-bg-color-overlay);
    .design-field-box {
        display: flex;
        padding: 10px;
        align-items: center;
        border: 1px dashed var(--el-border-color);
        border-radius: var(--el-border-radius-base);
        margin-bottom: 2px;
        cursor: pointer;
        user-select: none;
        .design-field {
            padding-right: 10px;
        }
        .design-field-name-input {
            width: 200px;
        }
        .design-field-name-comment {
            width: 100px;
        }
        .design-field-right {
            margin-left: auto;
        }
        &:hover {
            border-color: var(--el-color-primary);
        }
    }
    .design-field-box.activate {
        border-color: var(--el-color-primary);
    }
}
.field-inline {
    display: flex;
    :deep(.el-form-item) {
        width: 46%;
        margin-right: 2%;
    }
}
.field-config {
    overflow-x: auto;
    height: calc(100vh - 200px);
    padding: 20px;
    background-color: var(--ba-bg-color-overlay);
}
:deep(.confirm-generate-dialog) .el-dialog__body {
    height: unset;
}
.confirm-generate-dialog-body {
    padding: 30px;
}
.confirm-generate-dialog-footer {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
