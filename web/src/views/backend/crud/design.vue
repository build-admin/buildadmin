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
                                onChange: onTableNameChange,
                            }"
                            :error="state.error.tableName"
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
                        <el-link v-if="crudState.type != 'create'" @click="state.showDesignChangeLog = true" class="design-change-log" type="primary">
                            {{ t('crud.crud.Table design change') }}
                        </el-link>
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
                                    :label="item.name + (item.comment ? '-' + item.comment : item.title)"
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
                                        :label="item.name + (item.comment ? '-' + item.comment : item.title)"
                                        :value="item.name"
                                    />
                                </el-select>
                            </el-form-item>
                            <FormItem
                                class="default-sort-field-type"
                                :label="t('crud.crud.sort order')"
                                v-model="state.table.defaultSortType"
                                type="select"
                                :input-attr="{
                                    content: { desc: t('crud.crud.sort order desc'), asc: t('crud.crud.sort order asc') },
                                }"
                            />
                        </div>
                        <el-form-item :label-width="140" :label="t('crud.crud.Fields as Table Columns')">
                            <el-select :clearable="true" :multiple="true" class="w100" v-model="state.table.columnFields" placement="bottom">
                                <el-option
                                    v-for="(item, idx) in state.fields"
                                    :key="idx"
                                    :label="item.name + (item.comment ? '-' + item.comment : item.title)"
                                    :value="item.name"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item :label-width="140" :label="t('crud.crud.Fields as form items')">
                            <el-select :clearable="true" :multiple="true" class="w100" v-model="state.table.formFields" placement="bottom">
                                <el-option
                                    v-for="(item, idx) in state.fields"
                                    :key="idx"
                                    :label="item.name + (item.comment ? '-' + item.comment : item.title)"
                                    :value="item.name"
                                />
                            </el-select>
                        </el-form-item>
                        <FormItem
                            :label="t('crud.crud.The relative path to the generated code')"
                            v-model="state.table.generateRelativePath"
                            type="string"
                            :label-width="140"
                            :block-help="t('crud.crud.For quick combination code generation location, please fill in the relative path')"
                            :input-attr="{
                                onChange: onTableChange,
                            }"
                        />
                        <FormItem
                            :label="t('crud.crud.Generated Controller Location')"
                            v-model="state.table.controllerFile"
                            type="string"
                            :label-width="140"
                        />
                        <el-form-item :label="t('crud.crud.Generated Data Model Location')" :label-width="140">
                            <el-input v-model="state.table.modelFile" type="string">
                                <template #append>
                                    <el-checkbox
                                        @change="onChangeCommonModel"
                                        v-model="state.table.isCommonModel"
                                        :label="t('crud.crud.Common model')"
                                        size="small"
                                        :true-value="1"
                                        :false-value="0"
                                    />
                                </template>
                            </el-input>
                        </el-form-item>
                        <FormItem
                            :label="t('crud.crud.Generated Validator Location')"
                            v-model="state.table.validateFile"
                            type="string"
                            :label-width="140"
                        />
                        <FormItem :label="t('crud.crud.WEB end view directory')" v-model="state.table.webViewsDir" type="string" :label-width="140" />
                        <FormItem
                            :label="t('Database connection')"
                            v-model="state.table.databaseConnection"
                            type="remoteSelect"
                            :label-width="140"
                            :block-help="t('Database connection help')"
                            :input-attr="{
                                pk: 'key',
                                field: 'key',
                                remoteUrl: getDatabaseConnectionListUrl,
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
                    color="var(--el-text-color-primary)"
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
                <div ref="designWindowRef" class="design-window ba-scroll-style">
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
                                :model-value="field.name"
                                type="string"
                                :attr="{
                                    size: 'small',
                                    onInput: ($event: string) => onFieldNameChange($event, index),
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
                                    onChange: onFieldCommentChange,
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
                    <div class="design-field-empty" v-if="!state.fields.length && !state.draggingField">
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
                            <el-form-item :label="t('crud.crud.Generate type')">
                                <el-select
                                    @change="onFieldDesignTypeChange($event)"
                                    class="w100"
                                    :model-value="state.fields[state.activateField].designType"
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
                                    onChange: onFieldCommentChange,
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
                                :model-value="state.fields[state.activateField].name"
                                :input-attr="{
                                    onInput: ($event: string) => onFieldNameChange($event, state.activateField),
                                }"
                            />
                            <template v-if="state.fields[state.activateField].dataType">
                                <FormItem
                                    :label="t('crud.crud.Field Type')"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
                                    type="textarea"
                                    v-model="state.fields[state.activateField].dataType"
                                />
                            </template>
                            <template v-else>
                                <FormItem
                                    :label="t('crud.crud.Field Type')"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
                                    type="string"
                                    v-model="state.fields[state.activateField].type"
                                />
                                <div class="field-inline">
                                    <FormItem
                                        :label="t('crud.crud.length')"
                                        type="number"
                                        v-model="state.fields[state.activateField].length"
                                        :input-attr="{
                                            onChange: onFieldAttrChange,
                                        }"
                                    />
                                    <FormItem
                                        :label="t('crud.crud.decimal point')"
                                        type="number"
                                        v-model="state.fields[state.activateField].precision"
                                        :input-attr="{
                                            onChange: onFieldAttrChange,
                                        }"
                                    />
                                </div>
                            </template>
                            <el-form-item :label="t('crud.crud.Field Defaults')">
                                <el-select v-model="state.fields[state.activateField].defaultType">
                                    <el-option label="手动输入" value="INPUT" />
                                    <el-option label="EMPTY STRING（空字符串）" value="EMPTY STRING" />
                                    <el-option label="NULL" value="NULL" />
                                    <el-option label="无（不设默认值）" value="NONE" />
                                </el-select>
                                <el-input
                                    v-if="state.fields[state.activateField].defaultType == 'INPUT'"
                                    :placeholder="t('crud.crud.Please input the default value')"
                                    type="text"
                                    v-model="state.fields[state.activateField].default"
                                    @change="onFieldAttrChange"
                                    class="default-input"
                                />
                            </el-form-item>
                            <div class="field-inline">
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.state.Primary key')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].primaryKey"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
                                />
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Auto increment')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].autoIncrement"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
                                />
                            </div>
                            <div class="field-inline">
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Unsigned')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].unsigned"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
                                />
                                <FormItem
                                    class="form-item-position-right"
                                    :label="t('crud.crud.Allow NULL')"
                                    type="switch"
                                    v-model="state.fields[state.activateField].null"
                                    :input-attr="{
                                        onChange: onFieldAttrChange,
                                    }"
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
                                        :input-attr="{
                                            content: state.fields[state.activateField].table[idx].options ?? {},
                                            ...(state.fields[state.activateField].table[idx].attr ?? {}),
                                        }"
                                    />
                                </template>
                            </template>
                            <template v-if="!isEmpty(state.fields[state.activateField].form)">
                                <el-divider content-position="left">{{ t('crud.crud.Field Form Properties') }}</el-divider>
                                <template v-for="(item, idx) in state.fields[state.activateField].form" :key="idx">
                                    <FormItem
                                        v-if="item.type != 'hidden'"
                                        :label="$t('crud.crud.' + idx)"
                                        :type="item.type"
                                        v-model="state.fields[state.activateField].form[idx].value"
                                        :placeholder="state.fields[state.activateField].form[idx].placeholder ?? ''"
                                        :input-attr="{
                                            content: state.fields[state.activateField].form[idx].options ?? {},
                                            ...(state.fields[state.activateField].form[idx].attr ?? {}),
                                        }"
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
            :destroy-on-close="true"
            @keyup.enter="onSaveRemoteSelect"
        >
            <el-scrollbar max-height="60vh">
                <div class="ba-operate-form" :style="'width: calc(100% - 80px)'">
                    <el-form
                        ref="formRef"
                        :model="state.remoteSelectPre.form"
                        :rules="remoteSelectPreFormRules"
                        v-loading="state.remoteSelectPre.loading"
                        label-position="right"
                        label-width="160px"
                        v-if="state.remoteSelectPre.index != -1 && state.fields[state.remoteSelectPre.index]"
                    >
                        <FormItem
                            :label="t('crud.crud.Associated Data Table')"
                            v-model="state.remoteSelectPre.form.table"
                            type="remoteSelect"
                            :key="state.table.databaseConnection"
                            :input-attr="{
                                pk: 'table',
                                field: 'comment',
                                params: {
                                    connection: state.table.databaseConnection,
                                    samePrefix: 1,
                                    excludeTable: [
                                        'area',
                                        'token',
                                        'captcha',
                                        'admin_group_access',
                                        'config',
                                        'admin_log',
                                        'user_money_log',
                                        'user_score_log',
                                    ],
                                },
                                remoteUrl: getTableListUrl,
                                onChange: onJoinTableChange,
                            }"
                            prop="table"
                        />
                        <div v-loading="state.loading.remoteSelect">
                            <FormItem
                                prop="pk"
                                type="select"
                                :label="t('crud.crud.Drop down value field')"
                                v-model="state.remoteSelectPre.form.pk"
                                :placeholder="t('crud.crud.Please select the value field of the select component')"
                                :key="'select-value' + JSON.stringify(state.remoteSelectPre.fieldList)"
                                :input-attr="{
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
                                :input-attr="{
                                    content: state.remoteSelectPre.fieldList,
                                }"
                            />
                            <FormItem
                                v-if="state.fields[state.remoteSelectPre.index].designType == 'remoteSelect'"
                                prop="joinField"
                                type="selects"
                                :label="t('crud.crud.Fields displayed in the table')"
                                v-model="state.remoteSelectPre.form.joinField"
                                :placeholder="t('crud.crud.Please select the fields displayed in the table')"
                                :key="'join-field' + JSON.stringify(state.remoteSelectPre.fieldList)"
                                :input-attr="{
                                    content: state.remoteSelectPre.fieldList,
                                }"
                            />
                            <FormItem
                                :label="t('crud.crud.Data source configuration type')"
                                v-model="state.remoteSelectPre.form.sourceConfigType"
                                type="radio"
                                :input-attr="{
                                    border: true,
                                    content: {
                                        crud: t('crud.crud.Fast configuration with generated controllers and models'),
                                        custom: t('crud.crud.Custom configuration'),
                                    },
                                }"
                            />
                            <FormItem
                                v-if="state.remoteSelectPre.form.sourceConfigType == 'crud'"
                                prop="controllerFile"
                                type="select"
                                :label="t('crud.crud.Controller position')"
                                v-model="state.remoteSelectPre.form.controllerFile"
                                :placeholder="t('crud.crud.Please select the controller of the data table')"
                                :key="'controller-file' + JSON.stringify(state.remoteSelectPre.controllerFileList)"
                                :input-attr="{
                                    content: state.remoteSelectPre.controllerFileList,
                                }"
                                :block-help="
                                    t(
                                        'crud.crud.The remote pull-down will request the corresponding controller to obtain data, so it is recommended that you create the CRUD of the associated table'
                                    )
                                "
                            />

                            <!-- 数据源配置类型为CRUD时，模型位置必填 -->
                            <FormItem
                                :prop="state.remoteSelectPre.form.sourceConfigType == 'crud' ? 'modelFile' : ''"
                                type="select"
                                :label="t('crud.crud.Data Model Location')"
                                v-model="state.remoteSelectPre.form.modelFile"
                                :placeholder="t('crud.crud.Please select the data model location of the data table')"
                                :key="'model-file' + JSON.stringify(state.remoteSelectPre.modelFileList)"
                                :input-attr="{
                                    content: state.remoteSelectPre.modelFileList,
                                }"
                                :block-help="
                                    state.remoteSelectPre.form.sourceConfigType == 'crud'
                                        ? ''
                                        : t(
                                              'crud.crud.If it is left blank, the model of the associated table will be generated automatically If the table already has a model, it is recommended to select it to avoid repeated generation'
                                          )
                                "
                            />
                            <el-form-item
                                v-if="state.table.databaseConnection && state.remoteSelectPre.form.modelFile"
                                :label="t('Database connection')"
                            >
                                <el-text size="large" type="danger">{{ state.table.databaseConnection }}</el-text>
                                <div class="block-help">
                                    <div>{{ t('crud.crud.Check model class', { connection: state.table.databaseConnection }) }}</div>
                                    <div>{{ t('crud.crud.There is no connection attribute in model class') }}</div>
                                </div>
                            </el-form-item>
                            <FormItem
                                v-if="state.remoteSelectPre.form.sourceConfigType == 'custom'"
                                prop="remoteUrl"
                                :label="t('crud.crud.api url')"
                                type="string"
                                v-model="state.remoteSelectPre.form.remoteUrl"
                                :placeholder="t('crud.crud.api url example')"
                            />
                            <FormItem
                                v-if="state.remoteSelectPre.form.sourceConfigType == 'custom'"
                                :label="t('crud.crud.remote-primary-table-alias')"
                                type="string"
                                v-model="state.remoteSelectPre.form.primaryTableAlias"
                                :block-help="
                                    t(
                                        'crud.crud.If the remote interface query involves associated query of multiple tables, enter the alias of the primary data table here'
                                    )
                                "
                            >
                                <template #append>.{{ state.remoteSelectPre.form.pk }}</template>
                            </FormItem>
                            <el-form-item :label="t('Reminder')">
                                <div class="block-help">
                                    {{ t('crud.crud.Design remote select tips') }}
                                </div>
                            </el-form-item>
                        </div>
                    </el-form>
                </div>
            </el-scrollbar>
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
                <el-alert
                    v-if="showTableConflictConfirmGenerate()"
                    :title="
                        t(
                            'crud.crud.The data table already exists Continuing to generate will automatically delete the original table and create a new one!'
                        )
                    "
                    class="mt-10"
                    center
                    type="error"
                />
                <el-alert
                    v-if="state.confirmGenerate.menu"
                    :title="
                        t(
                            'crud.crud.The menu rule with the same name already exists The menu and permission node will not be created in this generation'
                        )
                    "
                    class="mt-10"
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
        <el-dialog class="ba-operate-dialog design-change-log-dialog" width="20%" v-model="state.showDesignChangeLog">
            <template #header>
                <div v-drag="['.design-change-log-dialog', '.el-dialog__header']">
                    {{ t('crud.crud.Data table design changes preview') }}
                </div>
            </template>
            <el-scrollbar max-height="400px">
                <template v-if="state.table.designChange.length">
                    <el-timeline class="design-change-log-timeline">
                        <el-timeline-item
                            v-for="(item, idx) in state.table.designChange"
                            :key="idx"
                            :type="getTableDesignTimelineType(item.type)"
                            :hollow="true"
                            :hide-timestamp="true"
                        >
                            <div class="design-timeline-box">
                                <el-checkbox v-model="item.sync" :label="getTableDesignChangeContent(item)" size="small" />
                            </div>
                        </el-timeline-item>
                    </el-timeline>
                    <span class="design-change-tips">{{ t('crud.crud.designChangeTips') }}</span>
                </template>
                <div class="design-change-tips" v-else>暂无表设计变更</div>
                <FormItem
                    :label="t('crud.crud.tableReBuild')"
                    class="rebuild-form-item"
                    v-model="state.table.rebuild"
                    type="radio"
                    :input-attr="{
                        border: true,
                        content: { No: t('crud.crud.No'), Yes: t('crud.crud.Yes') },
                    }"
                    :block-help="t('crud.crud.tableReBuildBlockHelp')"
                />
            </el-scrollbar>
            <template #footer>
                <div class="confirm-generate-dialog-footer">
                    <el-button @click="state.showDesignChangeLog = false">
                        {{ t('Confirm') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { useTemplateRefsList } from '@vueuse/core'
import type { FormItemRule, MessageHandler, TimelineItemProps } from 'element-plus'
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus'
import { cloneDeep, isEmpty, range } from 'lodash-es'
import type { SortableEvent } from 'sortablejs'
import Sortable from 'sortablejs'
import { nextTick, onMounted, reactive, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { generate, generateCheck, getFileData, parseFieldData, postLogStart, uploadCompleted, uploadLog } from '/@/api/backend/crud'
import { getDatabaseConnectionListUrl, getTableFieldList, getTableListUrl } from '/@/api/common'
import BaInput from '/@/components/baInput/index.vue'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import { useTerminal } from '/@/stores/terminal'
import { getArrayKey } from '/@/utils/common'
import { buildValidatorData, regularVarName } from '/@/utils/validate'
import { reloadServer } from '/@/utils/vite'
import type { FieldItem, TableDesignChange, TableDesignChangeType } from '/@/views/backend/crud/index'
import { changeStep, state as crudState, designTypes, fieldItem, getTableAttr, tableFieldsKey } from '/@/views/backend/crud/index'

let nameRepeatCount = 1
const { t } = useI18n()
const config = useConfig()
const terminal = useTerminal()
const formRef = useTemplateRef('formRef')
const tabsRefs = useTemplateRefsList<HTMLElement>()
const designWindowRef = useTemplateRef('designWindowRef')

const state: {
    loading: {
        init: boolean
        generate: boolean
        remoteSelect: boolean
    }
    sync: number
    table: {
        name: string
        comment: string
        quickSearchField: string[]
        defaultSortField: string
        formFields: string[]
        columnFields: string[]
        defaultSortType: string
        generateRelativePath: string
        isCommonModel: number
        modelFile: string
        controllerFile: string
        validateFile: string
        webViewsDir: string
        databaseConnection: string
        designChange: TableDesignChange[]
        rebuild: string
    }
    fields: FieldItem[]
    activateField: number
    fieldCollapseName: string[]
    remoteSelectPre: {
        show: boolean
        index: number
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
            sourceConfigType: 'crud' | 'custom'
            remoteUrl: string
            modelFile: string
            controllerFile: string
            primaryTableAlias: string
        }
    }
    showHeaderSeniorConfig: boolean
    confirmGenerate: {
        show: boolean
        menu: boolean
        table: boolean
        controller: boolean
    }
    draggingField: boolean
    showDesignChangeLog: boolean
    error: {
        tableName: string
        fieldName: MessageHandler | null
        fieldNameDuplication: MessageHandler | null
    }
} = reactive({
    loading: {
        init: false,
        generate: false,
        remoteSelect: false,
    },
    sync: 0,
    table: {
        name: '',
        comment: '',
        quickSearchField: [],
        defaultSortField: '',
        formFields: [],
        columnFields: [],
        defaultSortType: 'desc',
        generateRelativePath: '',
        isCommonModel: 0,
        modelFile: '',
        controllerFile: '',
        validateFile: '',
        webViewsDir: '',
        databaseConnection: '',
        designChange: [],
        rebuild: 'No',
    },
    fields: [],
    activateField: -1,
    fieldCollapseName: ['common', 'base', 'senior'],
    remoteSelectPre: {
        show: false,
        index: -1,
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
            sourceConfigType: 'crud',
            remoteUrl: '',
            modelFile: '',
            controllerFile: '',
            primaryTableAlias: '',
        },
    },
    showHeaderSeniorConfig: false,
    confirmGenerate: {
        show: false,
        menu: false,
        table: false,
        controller: false,
    },
    draggingField: false,
    showDesignChangeLog: false,
    error: {
        tableName: '',
        fieldName: null,
        fieldNameDuplication: null,
    },
})

type TableKey = keyof typeof state.table

const onActivateField = (idx: number) => {
    state.activateField = idx
}

const onFieldDesignTypeChange = (designType: string) => {
    // 获取新的类型的数据
    let fieldDesignData: FieldItem | null = null
    for (const key in fieldItem) {
        const fieldItemIndex = getArrayKey(fieldItem[key as keyof typeof fieldItem], 'designType', designType)
        if (fieldItemIndex !== false) {
            fieldDesignData = cloneDeep(fieldItem[key as keyof typeof fieldItem][fieldItemIndex])
            break
        }
    }

    if (!fieldDesignData) return false

    // 主键重复检查
    if (!primaryKeyRepeatCheck(fieldDesignData, state.activateField)) {
        return false
    }

    // 选中字段数据
    const field = cloneDeep(state.fields[state.activateField])

    // 赋值新类型
    field.designType = designType

    // 保留字段的 table 和 form 数据，此处额外处理以便交付给 handleFieldAttr 函数
    for (const tKey in field.table) {
        field.table[tKey] = field.table[tKey].value
    }
    for (const tKey in field.form) {
        field.form[tKey] = field.form[tKey].value
    }
    state.fields[state.activateField] = handleFieldAttr(field)

    // 询问是否切换至预设方案（除了字段名的属性全部重置）
    ElMessageBox.confirm(t('crud.crud.Reset generate type attr'), t('Reminder'), {
        confirmButtonText: t('Confirm') + t('Reset'),
        cancelButtonText: t('crud.crud.Design efficiency'),
        type: 'warning',
        closeOnClickModal: false,
    })
        .then(() => {
            // 记录字段属性更新
            onFieldAttrChange()

            // 重置属性，除了 name
            const oldName = state.fields[state.activateField].name
            state.fields[state.activateField] = handleFieldAttr(fieldDesignData)
            state.fields[state.activateField].name = oldName

            // 删除快速搜索和排序，根据新类型重新赋值
            clearFieldTableData(oldName)

            if (fieldDesignData.primaryKey) {
                // 设置为默认排序字段、快速搜索字段
                state.table.defaultSortField = fieldDesignData.name
                state.table.quickSearchField.push(fieldDesignData.name)
            }

            if (fieldDesignData.designType == 'weigh') {
                state.table.defaultSortField = fieldDesignData.name
            }

            // 远程下拉参数预填
            if (['remoteSelect', 'remoteSelects'].includes(fieldDesignData.designType)) {
                showRemoteSelectPre(state.activateField, true)
            }

            // 表单表格字段预定义
            if (!fieldDesignData.formBuildExclude) {
                state.table.formFields.push(fieldDesignData.name)
            }
            if (!fieldDesignData.tableBuildExclude) {
                state.table.columnFields.push(fieldDesignData.name)
            }
        })
        .catch(() => {})
}

/**
 * 字段名修改
 */
const onFieldNameChange = (val: string, index: number) => {
    const nameRepeatKey = getArrayKey(state.fields, 'name', val)
    if (nameRepeatKey !== false) {
        // 重命名失败，字段名称重复
        state.error.fieldNameDuplication = ElMessage({
            message: t('crud.crud.Rename failed') + '：' + t('crud.crud.Field name duplication', { field: val }),
            type: 'error',
        })
        return
    }

    const oldName = state.fields[index].name
    state.fields[index].name = val
    for (const key in tableFieldsKey) {
        for (const idx in state.table[tableFieldsKey[key] as TableKey] as string[]) {
            if ((state.table[tableFieldsKey[key] as TableKey] as string[])[idx] == oldName) {
                ;(state.table[tableFieldsKey[key] as TableKey] as string[])[idx] = val
            }
        }
    }
    if (state.table.defaultSortField && state.table.defaultSortField == oldName) {
        state.table.defaultSortField = val
    }
    logTableDesignChange({
        type: 'change-field-name',
        index: state.activateField,
        oldName: oldName,
        newName: val,
    })

    fieldNameCheck('ElMessage')
    fieldNameDuplicationCheck('ElMessage')
}

/**
 * 主键字段重复检测
 */
const primaryKeyRepeatCheck = (field: FieldItem, excludeIndex: number = -1) => {
    if (field.primaryKey === true) {
        const primaryKeyField = state.fields.find((item, index) => {
            if (excludeIndex > -1 && index == excludeIndex) {
                return false
            }
            return item.primaryKey
        })
        if (primaryKeyField) {
            ElNotification({
                type: 'error',
                message: t('crud.crud.There can only be one primary key field'),
            })
            return false
        }
    }
    return true
}

/**
 * 全部字段的名称命名规则检测
 */
const fieldNameCheck = (showErrorType: 'ElNotification' | 'ElMessage') => {
    if (state.error.fieldName) {
        state.error.fieldName.close()
        state.error.fieldName = null
    }
    for (const key in state.fields) {
        if (!regularVarName(state.fields[key].name)) {
            let msg = t(
                'crud.crud.Field name is invalid It starts with a letter or underscore and cannot contain any character other than letters, digits, or underscores',
                { field: state.fields[key].name }
            )
            if (showErrorType == 'ElMessage') {
                state.error.fieldName = ElMessage({
                    message: msg,
                    type: 'error',
                    duration: 0,
                })
            } else {
                ElNotification({
                    type: 'error',
                    message: msg,
                })
            }
            return false
        }
    }
    return true
}

/**
 * 全部字段的名称重复检测
 */
const fieldNameDuplicationCheck = (showErrorType: 'ElNotification' | 'ElMessage') => {
    if (state.error.fieldNameDuplication) {
        state.error.fieldNameDuplication.close()
        state.error.fieldNameDuplication = null
    }
    for (const key in state.fields) {
        let count = 0
        for (const checkKey in state.fields) {
            if (state.fields[key].name == state.fields[checkKey].name) {
                count++
            }
            if (count > 1) {
                let msg = t('crud.crud.Field name duplication', { field: state.fields[key].name })
                if (showErrorType == 'ElMessage') {
                    state.error.fieldNameDuplication = ElMessage({
                        message: msg,
                        type: 'error',
                        duration: 0,
                    })
                } else {
                    ElNotification({
                        type: 'error',
                        message: msg,
                    })
                }
                return false
            }
        }
    }
    return true
}

const onFieldAttrChange = () => {
    logTableDesignChange({
        type: 'change-field-attr',
        index: state.activateField,
        oldName: state.fields[state.activateField].name,
        newName: '',
    })
}

/**
 * 从 state.table.* 清理某个字段的数据
 */
const clearFieldTableData = (name: string) => {
    if (name == state.table.defaultSortField) {
        state.table.defaultSortField = ''
    }

    for (const key in tableFieldsKey) {
        const delIdx = (state.table[tableFieldsKey[key] as TableKey] as string[]).findIndex((item) => {
            return item == name
        })
        if (delIdx != -1) {
            ;(state.table[tableFieldsKey[key] as TableKey] as string[]).splice(delIdx, 1)
        }
    }
}

const onDelField = (index: number) => {
    if (!state.fields[index]) return
    state.activateField = -1

    clearFieldTableData(state.fields[index].name)

    logTableDesignChange({
        type: 'del-field',
        oldName: state.fields[index].name,
        newName: '',
    })

    state.fields.splice(index, 1)

    fieldNameCheck('ElMessage')
    fieldNameDuplicationCheck('ElMessage')
}

const showRemoteSelectPre = (index: number, hideDelField = false) => {
    state.remoteSelectPre.show = true
    state.remoteSelectPre.loading = true
    state.remoteSelectPre.index = index
    state.remoteSelectPre.hideDelField = hideDelField

    if (state.fields[index] && state.fields[index].form['remote-table'].value) {
        state.remoteSelectPre.form.table = state.fields[index].form['remote-table'].value
        state.remoteSelectPre.form.pk = state.fields[index].form['remote-pk'].value
        state.remoteSelectPre.form.label = state.fields[index].form['remote-field'].value
        state.remoteSelectPre.form.controllerFile = state.fields[index].form['remote-controller'].value
        state.remoteSelectPre.form.modelFile = state.fields[index].form['remote-model'].value
        state.remoteSelectPre.form.remoteUrl = state.fields[index].form['remote-url'].value
        state.remoteSelectPre.form.sourceConfigType = state.fields[index].form['remote-source-config-type'].value
        state.remoteSelectPre.form.primaryTableAlias = state.fields[index].form['remote-primary-table-alias'].value
        state.remoteSelectPre.form.joinField = state.fields[index].form['relation-fields'].value.split(',')
        getTableFieldList(state.fields[index].form['remote-table'].value, true, state.table.databaseConnection).then((res) => {
            const fieldSelect: anyObj = {}
            for (const key in res.data.fieldList) {
                fieldSelect[key] = (key ? key + ' - ' : '') + res.data.fieldList[key]
            }
            state.remoteSelectPre.fieldList = fieldSelect
        })
        if (isEmpty(state.remoteSelectPre.modelFileList) || isEmpty(state.remoteSelectPre.controllerFileList)) {
            getFileData(state.fields[index].form['remote-table'].value).then((res) => {
                state.remoteSelectPre.modelFileList = res.data.modelFileList
                state.remoteSelectPre.controllerFileList = res.data.controllerFileList
            })
        }
    }

    state.remoteSelectPre.loading = false
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
        type: crudState.type,
        table: state.table,
        fields: fields,
    })
        .then((res) => {
            const callback = () => {
                const webViewsDir = state.table.webViewsDir.replace(/^web/, '.')
                terminal.toggle(true)
                terminal.addTask('npx.prettier', false, webViewsDir, () => {
                    terminal.toggle(false)
                    terminal.toggleDot(true)
                    nextTick(() => {
                        // 要求 Vite 服务端重启
                        if (import.meta.hot) {
                            reloadServer('crud')
                        } else {
                            ElNotification({
                                type: 'error',
                                message: t('crud.crud.Vite hot warning'),
                            })
                        }
                    })
                })
            }

            if ((state.sync > 0 && config.crud.syncedUpdate === 'yes') || (state.sync == 0 && config.crud.syncType == 'automatic')) {
                uploadLog({
                    logs: [
                        {
                            ...res.data.crudLog,
                            public: config.crud.syncAutoPublic === 'yes' ? 1 : 0,
                            newLog: 1,
                        },
                    ],
                    save: 1,
                })
                    .then((res) => {
                        uploadCompleted({ syncIds: res.data.syncIds }).finally(() => {
                            callback()
                        })
                    })
                    .catch(() => {
                        callback()
                    })
            } else {
                callback()
            }
        })
        .finally(() => {
            state.loading.generate = false
            closeConfirmGenerate()
        })
}

const onGenerate = () => {
    // 字段名称检查
    if (!fieldNameCheck('ElNotification')) return
    if (!fieldNameDuplicationCheck('ElNotification')) return

    let msg = ''

    // 主键检查
    const pkIndex = state.fields.findIndex((item) => {
        return item.primaryKey
    })
    if (pkIndex === -1) {
        msg = t('crud.crud.Please design the primary key field!')
    }

    // 表名检查
    if (!state.table.name) msg = t('crud.crud.Please enter the data table name!')
    if (state.error.tableName) msg = t('crud.crud.Please enter the correct table name!')

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
        connection: state.table.databaseConnection,
        webViewsDir: state.table.webViewsDir,
        controllerFile: state.table.controllerFile,
    })
        .then(() => {
            startGenerate()
        })
        .catch((res) => {
            state.loading.generate = false
            if (res.code == -1) {
                state.confirmGenerate.menu = res.data.menu
                state.confirmGenerate.table = res.data.table
                state.confirmGenerate.controller = res.data.controller
                if (showTableConflictConfirmGenerate() || state.confirmGenerate.controller || state.confirmGenerate.menu) {
                    state.confirmGenerate.show = true
                } else {
                    startGenerate()
                }
            } else {
                ElNotification({
                    type: 'error',
                    message: res.msg,
                })
            }
        })
}

const showTableConflictConfirmGenerate = () => state.confirmGenerate.table && (crudState.type == 'create' || state.table.rebuild == 'Yes')

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

/**
 * 根据字段字典重新生成字段的数据类型
 */
const onFieldCommentChange = (comment: string) => {
    onFieldAttrChange()
    if (['enum', 'set'].includes(state.fields[state.activateField].type)) {
        if (!comment) {
            state.fields[state.activateField].dataType = `${state.fields[state.activateField].type}()`
            return
        }
        comment = comment.replaceAll('：', ':')
        comment = comment.replaceAll('，', ',')
        let comments = comment.split(':')
        if (comments[1]) {
            comments = comments[1].split(',')
            comments = comments
                .map((value) => {
                    if (!value) return ''
                    let temp = value.split('=')
                    if (temp[0] && temp[1]) {
                        return `'${temp[0]}'`
                    }
                    return ''
                })
                .filter((str: string) => str != '')

            // 字段数据类型
            state.fields[state.activateField].dataType = `${state.fields[state.activateField].type}(${comments.join(',')})`
        }
    }
}

const loadData = () => {
    tableDesignChangeInit()
    if (!['db', 'sql', 'log'].includes(crudState.type)) return

    state.loading.init = true

    // 从历史记录开始
    if (crudState.type == 'log') {
        postLogStart(crudState.startData.logId, crudState.startData.logType)
            .then((res) => {
                state.sync = res.data.sync
                state.table = res.data.table
                tableDesignChangeInit()
                if (res.data.table.empty) {
                    state.table.rebuild = 'Yes'
                }
                state.table.isCommonModel = parseInt(res.data.table.isCommonModel)
                state.table.databaseConnection = res.data.table.databaseConnection ? res.data.table.databaseConnection : ''
                const fields = res.data.fields
                for (const key in fields) {
                    const field = handleFieldAttr(cloneDeep(fields[key]))

                    // 默认值和默认值类型分析
                    if (typeof field.defaultType == 'undefined') {
                        if (field.default && ['none', 'null', 'empty string'].includes(field.default)) {
                            field.defaultType = field.default.toUpperCase() as 'EMPTY STRING' | 'NULL' | 'NONE'
                            field.default = ''
                        } else {
                            field.defaultType = 'INPUT'
                        }
                    }

                    state.fields.push(field)
                }
            })
            .finally(() => {
                state.loading.init = false
            })
        return
    }

    // 从数据表或sql开始
    parseFieldData({
        type: crudState.type,
        table: crudState.startData.table,
        sql: crudState.startData.sql,
        connection: crudState.startData.databaseConnection,
    })
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
            state.table.databaseConnection = crudState.startData.databaseConnection
            if (res.data.empty) {
                state.table.rebuild = 'Yes'
            }
            if (crudState.type == 'db' && crudState.startData.table) {
                state.table.name = crudState.startData.table
                onTableChange(crudState.startData.table)
            }
        })
        .finally(() => {
            state.loading.init = false
        })
}

/**
 * 字段名称重复时自动重命名
 */
const autoRenameRepeatField = (fieldName: string) => {
    const nameRepeatKey = getArrayKey(state.fields, 'name', fieldName)
    if (nameRepeatKey !== false) {
        fieldName += nameRepeatCount
        nameRepeatCount++
        return autoRenameRepeatField(fieldName)
    } else {
        return fieldName
    }
}

onMounted(() => {
    loadData()
    const sortable = Sortable.create(designWindowRef.value!, {
        group: 'design-field',
        animation: 200,
        filter: '.design-field-empty',
        onAdd: (evt: SortableEvt) => {
            const name = evt.originalEvent?.dataTransfer?.getData('name')
            const field = fieldItem[name as keyof typeof fieldItem]
            if (field && field[evt.oldIndex!]) {
                const data = handleFieldAttr(cloneDeep(field[evt.oldIndex!]))

                // 主键重复检测
                if (data.primaryKey) {
                    if (primaryKeyRepeatCheck(data)) {
                        // 设置为默认排序字段、快速搜索字段
                        state.table.defaultSortField = data.name
                        state.table.quickSearchField.push(data.name)
                    } else {
                        return evt.item.remove()
                    }
                }

                // 出现权重字段则以其排序
                if (data.designType == 'weigh') {
                    state.table.defaultSortField = data.name
                }

                // name 重复时，自动重命名
                data.name = autoRenameRepeatField(data.name)

                // 插入字段
                state.fields.splice(evt.newIndex!, 0, data)

                logTableDesignChange({
                    type: 'add-field',
                    index: evt.newIndex!,
                    newName: data.name,
                    oldName: '',
                    after: evt.newIndex === 0 ? 'FIRST FIELD' : state.fields[evt.newIndex! - 1].name,
                })

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

            logTableDesignChange({
                type: 'change-field-order',
                index: evt.newIndex!,
                newName: '',
                oldName: temp.name,
                after: evt.newIndex === 0 ? 'FIRST FIELD' : state.fields[evt.newIndex! - 1].name,
            })

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
            onStart: () => {
                state.draggingField = true
            },
            onEnd: () => {
                state.draggingField = false
            },
        })
    })
})

/**
 * 修改表名
 * @param val 新表名
 */
const onTableNameChange = (val: string) => {
    if (!val) return (state.error.tableName = '')
    if (/^[a-z_][a-z0-9_]*$/.test(val)) {
        state.error.tableName = ''
        onTableChange(val)
    } else {
        state.error.tableName = t('crud.crud.Use lower case underlined for table names')
    }
    tableDesignChangeInit()
}

const tableDesignChangeInit = () => {
    state.table.rebuild = 'No'
    state.table.designChange = []
}

/**
 * 预获取一个表的生成数据
 * @param val 新表名
 */
const onTableChange = (val: string) => {
    if (!val) return
    getFileData(val, state.table.isCommonModel).then((res) => {
        state.table.modelFile = res.data.modelFile
        state.table.controllerFile = res.data.controllerFile
        state.table.validateFile = res.data.validateFile
        state.table.webViewsDir = res.data.webViewsDir
        state.table.generateRelativePath = val.replaceAll('/', '\\')
    })
}

const onChangeCommonModel = () => {
    onTableChange(state.table.generateRelativePath)
}

const onJoinTableChange = () => {
    if (!state.remoteSelectPre.form.table) return

    // 重置远程下拉信息表单
    resetRemoteSelectForm(['table'])

    state.loading.remoteSelect = true
    getTableFieldList(state.remoteSelectPre.form.table, true, state.table.databaseConnection)
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

    getFileData(state.remoteSelectPre.form.table).then((res) => {
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
        // 修改字段名
        if (state.fields[state.remoteSelectPre.index].name == 'remote_select') {
            const newName =
                state.remoteSelectPre.form.table + (state.fields[state.remoteSelectPre.index].designType == 'remoteSelect' ? '_id' : '_ids')
            onFieldNameChange(newName, state.remoteSelectPre.index)
        }

        state.fields[state.remoteSelectPre.index].form['remote-table'].value = state.remoteSelectPre.form.table
        state.fields[state.remoteSelectPre.index].form['remote-pk'].value = state.remoteSelectPre.form.pk
        state.fields[state.remoteSelectPre.index].form['remote-field'].value = state.remoteSelectPre.form.label
        state.fields[state.remoteSelectPre.index].form['remote-controller'].value = state.remoteSelectPre.form.controllerFile
        state.fields[state.remoteSelectPre.index].form['remote-model'].value = state.remoteSelectPre.form.modelFile
        state.fields[state.remoteSelectPre.index].form['remote-url'].value = state.remoteSelectPre.form.remoteUrl
        state.fields[state.remoteSelectPre.index].form['remote-source-config-type'].value = state.remoteSelectPre.form.sourceConfigType
        state.fields[state.remoteSelectPre.index].form['remote-primary-table-alias'].value = state.remoteSelectPre.form.primaryTableAlias

        state.fields[state.remoteSelectPre.index].form['relation-fields'].value =
            state.fields[state.remoteSelectPre.index].designType == 'remoteSelect'
                ? state.remoteSelectPre.form.joinField.join(',')
                : state.remoteSelectPre.form.label

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

const resetRemoteSelectForm = (excludes: string[] = []) => {
    for (const key in state.remoteSelectPre.form) {
        if (excludes.includes(key)) continue
        if (key == 'joinField') {
            state.remoteSelectPre.form[key] = []
        } else if (key == 'sourceConfigType') {
            state.remoteSelectPre.form[key] = 'crud'
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
    modelFile: [buildValidatorData({ name: 'required', title: t('crud.crud.Data Model Location') })],
    remoteUrl: [buildValidatorData({ name: 'required', title: t('crud.crud.remote-url') })],
})

const logTableDesignChange = (data: TableDesignChange) => {
    if (crudState.type == 'create') return
    let push = true
    if (data.type == 'change-field-name') {
        for (const key in state.table.designChange) {
            // 有属性修改记录的字段被改名-单独循环防止字段再次改名后造成找不到属性修改记录
            if (state.table.designChange[key].type == 'change-field-attr' && data.oldName == state.table.designChange[key].oldName) {
                state.table.designChange[key].oldName = data.newName
            }
            // 有排序记录的字段被改名
            if (state.table.designChange[key].type == 'change-field-order' && data.oldName == state.table.designChange[key].oldName) {
                state.table.designChange[key].oldName = data.newName
            }
            if (state.table.designChange[key].after == data.oldName) {
                state.table.designChange[key].after = data.newName
            }
        }
        for (const key in state.table.designChange) {
            // 新增字段改名
            if (state.table.designChange[key].type == 'add-field' && state.table.designChange[key].newName == data.oldName) {
                state.table.designChange[key].newName = data.newName
                push = false
                // 同一字段不会有两条新增记录
                break
            }
            // 字段再次改名
            if (state.table.designChange[key].type == 'change-field-name' && state.table.designChange[key].newName == data.oldName) {
                data.oldName = state.table.designChange[key].oldName
                state.table.designChange[key] = data

                // 取消字段改名
                if (state.table.designChange[key].newName == state.table.designChange[key].oldName) {
                    state.table.designChange.splice(key as any, 1)
                }

                push = false
                break
            }
        }
    } else if (data.type == 'del-field') {
        let add = false
        state.table.designChange = state.table.designChange.filter((item) => {
            // 新增的字段被删除
            add = item.type == 'add-field' && item.newName == data.oldName
            // 有改名记录的字段被删除
            const name = item.type == 'change-field-name' && item.newName == data.oldName
            // 有属性修改记录的字段被删除
            const attr = item.type == 'change-field-attr' && item.oldName == data.oldName
            // 有排序记录的字段被删除
            const order = item.type == 'change-field-order' && item.oldName == data.oldName

            if (name) data.oldName = item.oldName

            return !add && !name && !attr && !order
        })

        // 添加的字段需要过滤掉记录同时不记录删除操作
        if (add) push = false

        for (const key in state.table.designChange) {
            // 同一字段名称多次删除（删除后添加再删除）
            if (state.table.designChange[key].type == 'del-field' && state.table.designChange[key].oldName == data.oldName) {
                push = false
                break
            }
        }
    } else if (data.type == 'change-field-attr') {
        // 先改名再改属性无需处理
        for (const key in state.table.designChange) {
            // 重复修改属性只记录一次
            if (state.table.designChange[key].type == 'change-field-attr' && state.table.designChange[key].oldName == data.oldName) {
                push = false
                break
            }
            // 新增的字段无需记录属性修改
            if (state.table.designChange[key].type == 'add-field' && state.table.designChange[key].newName == data.oldName) {
                push = false
                break
            }
        }
    } else if (data.type == 'change-field-order') {
        for (const key in state.table.designChange) {
            // 新增的字段
            if (state.table.designChange[key].type == 'add-field' && state.table.designChange[key].newName == data.oldName) {
                // 更新排序设定
                state.table.designChange[key].after = data.after
                push = false
                break
            }
            // 重复的排序记录
            if (state.table.designChange[key].type == 'change-field-order' && state.table.designChange[key].oldName == data.oldName) {
                state.table.designChange[key] = data
                push = false
                break
            }
        }
    }
    data.sync = true
    if (push) state.table.designChange.push(data)
}

const getTableDesignChangeContent = (data: TableDesignChange): string => {
    switch (data.type) {
        case 'add-field':
            return t('crud.crud.Add field') + ' ' + data.newName
        case 'change-field-attr':
            return t('crud.crud.Modify field properties') + ' ' + data.oldName
        case 'change-field-name':
            return t('crud.crud.Modify field name') + ' ' + data.oldName + ' => ' + data.newName
        case 'del-field':
            return t('crud.crud.Delete field') + ' ' + data.oldName
        case 'change-field-order':
            return (
                t('crud.crud.Modify field order') +
                ' ' +
                data.oldName +
                ' => ' +
                (data.after == 'FIRST FIELD' ? t('crud.crud.First field') : data.after + ' ' + t('crud.crud.After'))
            )
        default:
            return t('Unknown')
    }
}

const getTableDesignTimelineType = (type: TableDesignChangeType): TimelineItemProps['type'] => {
    let timeline = ''
    switch (type) {
        case 'change-field-name':
            timeline = 'warning'
            break
        case 'del-field':
            timeline = 'danger'
            break
        case 'add-field':
            timeline = 'primary'
            break
        case 'change-field-attr':
            timeline = 'success'
            break
        case 'change-field-order':
            timeline = 'info'
            break
        default:
            timeline = 'success'
            break
    }
    return timeline as TimelineItemProps['type']
}
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
.mt-10 {
    margin-top: 10px;
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
    padding: 3px 16px;
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
        color: var(--el-text-color-primary);
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
    height: v-bind("state.error.tableName ? '70px':'60px'");
    padding: 10px;
    background-color: var(--ba-bg-color-overlay);
    border-radius: var(--el-border-radius-base);
    transition: 0.1s;
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
        .design-change-log {
            margin-right: 10px;
        }
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
    border: v-bind('state.draggingField ? "1px dashed var(--el-color-primary)":(state.fields.length ? "none":"1px dashed var(--el-border-color)")');
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
.default-input {
    margin-top: 10px;
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
:deep(.design-change-log-dialog) .el-dialog__body {
    height: unset;
    padding-top: 20px;
    .design-change-log-timeline {
        padding-left: 10px;
        .el-timeline-item .el-timeline-item__node {
            top: 3px;
        }
    }
    .design-change-tips {
        display: block;
        margin-bottom: 20px;
        color: var(--el-color-info);
        font-size: var(--el-font-size-small);
    }
    .rebuild-form-item {
        padding-top: 20px;
        border-top: 1px solid var(--el-border-color-lighter);
    }
}
</style>
