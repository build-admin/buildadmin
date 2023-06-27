<template>
    <div class="default-main">
        <div class="crud-title">{{ t('crud.crud.start') }}</div>
        <div class="start-opt">
            <el-row :gutter="20">
                <el-col :xs="24" :span="8">
                    <div @click="changeStep('create')" class="start-item suspension">
                        <div class="start-item-title">{{ t('crud.crud.create') }}</div>
                        <div class="start-item-remark">{{ t('crud.crud.New background CRUD from zero') }}</div>
                    </div>
                </el-col>
                <el-col @click="onShowDialog('db')" :xs="24" :span="8">
                    <div class="start-item suspension">
                        <div class="start-item-title">{{ t('crud.crud.Select Data Table') }}</div>
                        <div class="start-item-remark">{{ t('crud.crud.Select a designed data table from the database') }}</div>
                    </div>
                </el-col>
                <el-col @click="state.showLog = true" :xs="24" :span="8">
                    <div class="start-item suspension">
                        <div class="start-item-title">{{ t('crud.crud.CRUD record') }}</div>
                        <div class="start-item-remark">{{ t('crud.crud.Start with previously generated CRUD code') }}</div>
                    </div>
                </el-col>
            </el-row>
            <el-row justify="center">
                <el-col :span="20" class="ba-markdown crud-tips suspension">
                    <b>{{ t('crud.crud.Fast experience') }}</b>
                    <ol>
                        <li>
                            {{ t('crud.crud.experience 1 1') }}
                            <a target="_blank" href="https://wonderful-code.gitee.io/guide/other/developerMustSee.html">
                                {{ t('crud.crud.experience 1 2') }}
                            </a>
                            {{ t('crud.crud.experience 1 3') }}
                        </li>
                        <li>
                            {{ t('crud.crud.experience 2 1') }}
                            <code>{{ t('crud.crud.experience 2 2') }}</code>
                            {{ t('crud.crud.experience 2 3') }}<code>test_build</code>{{ t('crud.crud.data sheet') }}
                        </li>
                        <li>
                            {{ t('crud.crud.experience 3 1') }}<code>{{ t('crud.crud.experience 3 2') }}</code> {{ t('crud.crud.experience 3 3')
                            }}<code>{{ t('crud.crud.experience 3 4') }}</code>
                        </li>
                    </ol>
                </el-col>
            </el-row>

            <el-dialog
                class="ba-operate-dialog select-db-dialog"
                v-model="state.dialog.visible"
                :title="state.dialog.type == 'sql' ? t('crud.crud.Please enter SQL') : t('crud.crud.Please select a data table')"
                :destroy-on-close="true"
            >
                <el-form
                    :label-width="140"
                    @keyup.enter="onSubmit(formRef)"
                    class="select-db-form"
                    ref="formRef"
                    :model="crudState.startData"
                    :rules="rules"
                >
                    <template v-if="state.dialog.type == 'sql'">
                        <el-input
                            class="sql-input"
                            prop="sql"
                            ref="sqlInputRef"
                            v-model="crudState.startData.sql"
                            type="textarea"
                            :placeholder="t('crud.crud.table create SQL')"
                            :rows="10"
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="onSubmit(formRef)"
                        />
                    </template>
                    <template v-else-if="state.dialog.type == 'db'">
                        <FormItem
                            :label="t('crud.crud.data sheet')"
                            class="select-db"
                            v-model="crudState.startData.db"
                            type="select"
                            :key="JSON.stringify(state.dialog.dbList)"
                            :placeholder="t('crud.crud.Please select a data table')"
                            :data="{
                                content: state.dialog.dbList,
                            }"
                            :attr="{
                                blockHelp: t('crud.crud.data sheet help'),
                            }"
                            :input-attr="{
                                onChange: onDbStartChange,
                            }"
                            prop="db"
                        />
                        <el-alert
                            v-if="state.successRecord"
                            class="success-record-alert"
                            :title="t('crud.crud.The selected table has already generated records You are advised to start with historical records')"
                            :show-icon="true"
                            :closable="false"
                            type="warning"
                        />
                    </template>
                </el-form>
                <template #footer>
                    <div :style="{ width: 'calc(100% * 0.9)' }">
                        <el-button @click="state.dialog.visible = false">{{ $t('Cancel') }}</el-button>
                        <el-button :loading="state.loading" @click="onSubmit(formRef)" v-blur type="primary">{{ t('Confirm') }}</el-button>
                        <el-button v-if="state.successRecord" @click="onLogStart" v-blur type="success">
                            {{ t('crud.crud.Start with the historical record') }}
                        </el-button>
                    </div>
                </template>
            </el-dialog>

            <CrudLog v-model="state.showLog" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { getDatabaseList, checkCrudLog } from '/@/api/backend/crud'
import FormItem from '/@/components/formItem/index.vue'
import { changeStep, state as crudState } from '/@/views/backend/crud/index'
import { ElNotification, FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import CrudLog from '/@/views/backend/crud/log.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const sqlInputRef = ref()
const formRef = ref<FormInstance>()
const state = reactive({
    dialog: {
        type: '',
        visible: false,
        dbList: [],
    },
    showLog: false,
    loading: false,
    successRecord: 0,
})

const onShowDialog = (type: string) => {
    state.dialog.type = type
    state.dialog.visible = true
    if (type == 'sql') {
        setTimeout(() => {
            sqlInputRef.value.focus()
        }, 200)
    } else if (type == 'db') {
        getDatabaseList().then((res) => {
            state.dialog.dbList = res.data.dbs
        })
    }
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    db: [buildValidatorData({ name: 'required', message: t('crud.crud.Please select a data table') })],
})

const onSubmit = (formEl: FormInstance | undefined = undefined) => {
    if (!formEl) return
    if (state.dialog.type == 'sql' && !crudState.startData.sql) {
        ElNotification({
            type: 'error',
            message: t('crud.crud.Please enter the table creation SQL'),
        })
        return
    }
    formEl.validate((valid) => {
        if (valid) {
            changeStep(state.dialog.type)
        }
    })
}

const onDbStartChange = () => {
    if (crudState.startData.db) {
        // 检查是否有CRUD记录
        state.loading = true
        checkCrudLog(crudState.startData.db)
            .then((res) => {
                state.successRecord = res.data.id
            })
            .finally(() => {
                state.loading = false
            })
    }
}

const onLogStart = () => {
    if (state.successRecord) {
        crudState.startData.logId = state.successRecord.toString()
        changeStep('log')
    }
}
</script>

<style scoped lang="scss">
:deep(.select-db-dialog) .el-dialog__body {
    height: unset;
    .select-db-form {
        width: 88%;
    }
    .success-record-alert {
        width: calc(100% - 140px);
        margin-left: 140px;
        margin-bottom: 30px;
        margin-top: -30px;
    }
}
.crud-title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--el-font-size-extra-large);
    font-weight: bold;
    padding-top: 120px;
}
.start-opt {
    display: block;
    width: 60%;
    margin: 40px auto;
}
.start-item {
    background-color: #e1eaf9;
    border-radius: var(--el-border-radius-base);
    padding: 25px;
    margin-bottom: 20px;
    cursor: pointer;
}
.start-item-title {
    font-size: var(--el-font-size-large);
    color: var(--ba-color-primary-light);
}
.start-item-remark {
    display: block;
    line-height: 18px;
    min-height: 48px;
    padding-top: 12px;
    color: #92969a;
}
.sql-input {
    margin: 20px 0;
}
.select-db {
    margin: 40px 0;
}
.crud-tips {
    margin-top: 60px;
    padding: 20px;
    background-color: rgba($color: #ffffff, $alpha: 0.6);
    border-radius: var(--el-border-radius-base);
    color: var(--el-color-info);
    b {
        font-size: 15px;
        padding-left: 10px;
    }
}
@at-root .dark {
    .start-item {
        background-color: #1d1e1f;
    }
    .crud-tips {
        background-color: rgba($color: #1d1e1f, $alpha: 0.4);
    }
}
</style>
