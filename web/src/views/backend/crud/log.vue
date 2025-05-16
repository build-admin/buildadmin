<template>
    <div>
        <el-dialog
            @close="emits('update:modelValue', false)"
            width="70%"
            :model-value="modelValue"
            class="ba-crud-log-dialog"
            :title="t('crud.crud.CRUD record')"
            :append-to-body="true"
            :destroy-on-close="true"
        >
            <TableHeader :buttons="['refresh', 'quickSearch', 'columnDisplay']" :quick-search-placeholder="t('crud.log.quick Search Fields')">
                <template v-if="baAccount.token">
                    <el-tooltip :content="t('crud.log.Upload the selected design records to the cloud for cross-device use')" placement="top">
                        <el-button
                            v-blur
                            :disabled="baTable.table.selection!.length > 0 ? false : true"
                            @click="onUpload"
                            class="table-header-operate"
                            type="success"
                        >
                            <Icon color="#ffffff" name="fa fa-cloud-upload" />
                            <span class="table-header-operate-text">{{ t('Upload') }}</span>
                        </el-button>
                    </el-tooltip>
                    <el-tooltip :content="t('crud.log.Design records that have been synchronized to the cloud')" placement="top">
                        <el-button v-blur class="table-header-operate" @click="onLoadLogs" type="success">
                            <Icon color="#ffffff" name="fa fa-cloud-download" />
                            <span class="table-header-operate-text">{{ t('crud.log.Cloud record') }}</span>
                        </el-button>
                    </el-tooltip>
                    <el-button v-blur @click="toggleShowConfig(true)" class="table-header-operate" type="primary">
                        <Icon name="fa fa-gear" />
                        <span class="table-header-operate-text">{{ t('crud.log.Settings') }}</span>
                    </el-button>
                    <el-button v-blur @click="toggleShowBaAccount(true)" class="table-header-operate" type="primary">
                        <Icon name="fa fa-user-o" />
                        <span class="table-header-operate-text">{{ t('layouts.Member information') }}</span>
                    </el-button>
                </template>
                <template v-else>
                    <el-button v-blur @click="toggleShowBaAccount(true)" class="table-header-operate" type="primary">
                        <Icon name="fa fa-chain" />
                        <span class="table-header-operate-text">{{ t('crud.log.Login for backup design') }}</span>
                    </el-button>
                </template>
            </TableHeader>
            <Table ref="tableRef">
                <template #tableName>
                    <el-table-column :show-overflow-tooltip="true" prop="table_name" align="center" :label="t('crud.log.table_name')">
                        <template #default="scope">
                            {{ (scope.row.table.databaseConnection ? scope.row.table.databaseConnection + '.' : '') + scope.row.table.name }}
                        </template>
                    </el-table-column>
                </template>
                <template #sync>
                    <el-table-column prop="sync" align="center" :label="t('crud.log.sync')">
                        <template #default="scope">
                            <el-tag :type="scope.row.sync > 0 ? 'primary' : 'info'">
                                {{ scope.row.sync > 0 ? t('crud.log.sync yes') : t('crud.log.sync no') }}
                            </el-tag>
                        </template>
                    </el-table-column>
                </template>
            </Table>
        </el-dialog>

        <el-dialog v-model="state.showConfig" :title="t('crud.log.Settings')">
            <div class="ba-operate-form" :style="config.layout.shrink ? '' : 'width: calc(100% - 90px)'">
                <el-form @keyup.enter="onConfigSubmit" :model="state.configForm" label-position="top">
                    <FormItem
                        :label="t('crud.log.CRUD design record synchronization scheme')"
                        v-model="state.configForm.syncType"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { manual: t('crud.log.Manual'), automatic: t('crud.log.automatic') },
                        }"
                        :block-help="t('crud.log.You can use the synchronized design records across devices')"
                    />
                    <FormItem
                        :key="state.configForm.syncType"
                        v-if="state.configForm.syncType == 'automatic'"
                        :label="t('crud.log.When automatically synchronizing records, share them to the open source community')"
                        v-model="state.configForm.syncAutoPublic"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { no: t('crud.log.Not to share'), yes: t('crud.log.Share') },
                        }"
                        :block-help="t('crud.log.Enabling sharing can automatically earn community points during development')"
                    />
                    <FormItem
                        :label="t('crud.log.The synchronized CRUD records are automatically resynchronized when they are updated')"
                        v-model="state.configForm.syncedUpdate"
                        type="radio"
                        :input-attr="{
                            border: true,
                            content: { no: t('crud.log.Do not resynchronize'), yes: t('crud.log.Automatic resynchronization') },
                        }"
                    />
                </el-form>
            </div>
            <template #footer>
                <div :style="'width: calc(100% - 90px)'">
                    <el-button @click="toggleShowConfig(false)">{{ t('Cancel') }}</el-button>
                    <el-button v-blur @click="onConfigSubmit" type="primary"> {{ t('Save') }} </el-button>
                </div>
            </template>
        </el-dialog>

        <el-dialog v-model="state.showUpload" :title="t('Upload')" width="60%">
            <div class="ba-operate-form" v-loading="state.uploadValidLoading">
                <el-table :empty-text="t('crud.log.No effective design')" :data="state.uploadValidData" stripe class="w100">
                    <el-table-column prop="table_name" :label="t('crud.log.table_name')" align="center" />
                    <el-table-column prop="comment" :label="t('crud.log.comment')" align="center" show-overflow-tooltip />
                    <el-table-column prop="fieldCount" :label="t('crud.log.Number of fields')" align="center" />
                    <el-table-column :label="t('crud.log.Upload type')" align="center">
                        <template #default="scope">
                            <el-tag :type="scope.row.id > 0 ? 'primary' : 'success'">
                                {{ scope.row.id > 0 ? t('crud.log.Update') : t('crud.log.New added') }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="score" :label="t('crud.log.Share to earn points')" align="center">
                        <template #default="scope">
                            <el-text :type="scope.row.score <= 0 ? 'info' : 'success'">{{ scope.row.score }}</el-text>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('crud.log.Share to the open source community')" align="center">
                        <template #default="scope">
                            <el-switch v-model="scope.row.public" />
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <template #footer>
                <div :style="'width: calc(100% - 90px)'">
                    <el-button @click="toggleShowUpload(false)">{{ t('Cancel') }}</el-button>
                    <el-button v-blur @click="onSaveUpload" type="primary"> {{ t('Upload') }} </el-button>
                </div>
            </template>
        </el-dialog>

        <el-dialog v-model="state.showDownload" :title="t('crud.log.Cloud record')">
            <div class="download-table-header">
                <el-button v-blur @click="onLoadLogs" color="#40485b" class="download-table-header-operate" type="info">
                    <Icon color="#fff" size="14" name="fa fa-refresh" />
                </el-button>

                <el-popconfirm
                    @confirm="onBatchDelLog"
                    :confirm-button-text="t('Delete')"
                    :cancel-button-text="t('Cancel')"
                    confirmButtonType="danger"
                    :title="t('Are you sure to delete the selected record?')"
                    :disabled="state.downloadSelection.length > 0 ? false : true"
                >
                    <template #reference>
                        <el-button
                            v-blur
                            :disabled="state.downloadSelection.length > 0 ? false : true"
                            class="download-table-header-operate"
                            type="danger"
                        >
                            <Icon color="#fff" size="14" name="fa fa-trash" />
                            <span class="download-table-header-operate-text">{{ t('Delete') }}</span>
                        </el-button>
                    </template>
                </el-popconfirm>

                <div class="download-table-search">
                    <el-input
                        v-model="state.downloadQuickSearch"
                        class="xs-hidden download-quick-search"
                        @input="onSearchDownloadInput"
                        :placeholder="t('Search')"
                        clearable
                    />
                </div>
            </div>
            <el-table
                v-loading="state.downloadLoading"
                @selection-change="onSelectionChange"
                :empty-text="t('crud.log.No design record')"
                :data="state.downloadData"
                stripe
                class="w100"
            >
                <el-table-column type="selection" align="center" />
                <el-table-column :show-overflow-tooltip="true" align="center" :label="t('crud.log.table_name')">
                    <template #default="scope">
                        {{ (scope.row.table.databaseConnection ? scope.row.table.databaseConnection + '.' : '') + scope.row.table.name }}
                    </template>
                </el-table-column>
                <el-table-column prop="comment" :label="t('crud.log.comment')" align="center" show-overflow-tooltip />
                <el-table-column :label="t('crud.log.Field')" align="center">
                    <template #default="scope">
                        <el-popover :width="460" class="box-item" :title="t('crud.log.Field information')" placement="left">
                            <template #reference>
                                <el-text class="cp" type="primary">{{ scope.row.fieldCount }}</el-text>
                            </template>
                            <el-table :empty-text="t('crud.log.No field')" :data="scope.row.fields" stripe class="w100">
                                <el-table-column prop="name" :label="t('crud.log.Field name')" align="center" />
                                <el-table-column prop="comment" :label="t('crud.log.Note')" align="center" show-overflow-tooltip />
                                <el-table-column :label="t('crud.log.Type')" align="center" show-overflow-tooltip>
                                    <template #default="typeScope">
                                        <el-text>{{ typeScope.row.dataType ?? typeScope.row.type }}</el-text>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column :label="t('Operate')" align="center">
                    <template #default="scope">
                        <el-popconfirm :title="t('crud.crud.Start CRUD design with this record?')" @confirm="onLoadLog(scope.row.id)">
                            <template #reference>
                                <el-button type="primary" link>
                                    <div>{{ t('crud.log.Load') }}</div>
                                </el-button>
                            </template>
                        </el-popconfirm>
                        <el-popconfirm :title="t('crud.log.Delete cloud records?')" @confirm="onDelLog([scope.row.id])">
                            <template #reference>
                                <el-button type="danger" link>
                                    <div>{{ t('Delete') }}</div>
                                </el-button>
                            </template>
                        </el-popconfirm>
                    </template>
                </el-table-column>
            </el-table>
            <div class="log-pagination">
                <el-pagination
                    :currentPage="state.downloadPage"
                    :page-size="10"
                    background
                    :layout="config.layout.shrink ? 'prev, next, jumper' : 'total, ->, prev, pager, next, jumper'"
                    :total="state.downloadTotal"
                    @current-change="onDownloadCurrentChange"
                ></el-pagination>
            </div>
        </el-dialog>

        <BaAccountDialog v-model="state.showBaAccount" :login-callback="onBaAccountLoginSuccess" />
    </div>
</template>

<script setup lang="ts">
import { ElNotification } from 'element-plus'
import { debounce } from 'lodash-es'
import { nextTick, onMounted, provide, reactive, useTemplateRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { delLog, logs, postDel, uploadCompleted, uploadLog } from '/@/api/backend/crud'
import { baTableApi } from '/@/api/common'
import FormItem from '/@/components/formItem/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import BaAccountDialog from '/@/layouts/backend/components/baAccount.vue'
import { useBaAccount } from '/@/stores/baAccount'
import { useConfig } from '/@/stores/config'
import baTableClass from '/@/utils/baTable'
import { auth, getArrayKey } from '/@/utils/common'
import { changeStep, state as crudState } from '/@/views/backend/crud/index'

interface Props {
    modelValue: boolean
}

const config = useConfig()
const baAccount = useBaAccount()
const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

const state = reactive({
    ready: false,
    configForm: {
        syncType: config.crud.syncType,
        syncedUpdate: config.crud.syncedUpdate,
        syncAutoPublic: config.crud.syncAutoPublic,
    },
    showUpload: false,
    showConfig: false,
    showDownload: false,
    showBaAccount: false,
    uploadScoreSum: 0,
    uploadValidData: [] as anyObj[],
    uploadValidLoading: false,
    downloadPage: 1,
    downloadData: [] as anyObj[],
    downloadTotal: 0,
    downloadLoading: false,
    downloadSelection: [] as anyObj[],
    downloadQuickSearch: '',
})

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')

const optButtons: OptButton[] = [
    {
        render: 'confirmButton',
        name: 'copy',
        title: 'crud.crud.copy',
        text: '',
        type: 'primary',
        icon: 'fa fa-copy',
        class: 'table-row-copy',
        popconfirm: {
            confirmButtonText: t('Confirm'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'primary',
            title: t('crud.crud.Start CRUD design with this record?'),
            width: '220px',
        },
        disabledTip: false,
        click: (row) => {
            crudState.startData.logId = row[baTable.table.pk!]
            changeStep('log')
            emits('update:modelValue', false)
        },
    },
    {
        render: 'confirmButton',
        name: 'del',
        title: 'crud.log.delete',
        text: '',
        type: 'danger',
        icon: 'fa fa-trash',
        class: 'table-row-delete',
        popconfirm: {
            confirmButtonText: t('crud.crud.Delete Code'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'danger',
            title: t('crud.crud.Are you sure to delete the generated CRUD code?'),
            width: '248px',
        },
        disabledTip: false,
        click: (row) => {
            postDel(row[baTable.table.pk!]).then(() => {
                baTable.onTableHeaderAction('refresh', {})
            })
        },
        display: (row) => {
            return row['status'] != 'delete' && auth('delete')
        },
    },
]
const baTable = new baTableClass(
    new baTableApi('/admin/crud.Log/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('crud.log.id'), prop: 'id', align: 'center', width: 70, operator: '=', sortable: 'custom' },
            {
                label: t('crud.log.table_name'),
                operator: 'LIKE',
                render: 'slot',
                slotName: 'tableName',
            },
            {
                label: t('crud.log.comment'),
                prop: 'comment',
                align: 'center',
                showOverflowTooltip: true,
                operator: 'LIKE',
            },
            {
                label: t('crud.log.sync'),
                prop: 'sync',
                align: 'center',
                render: 'slot',
                slotName: 'sync',
            },
            {
                label: t('crud.log.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                sortable: false,
                replaceValue: {
                    delete: t('crud.log.status delete'),
                    success: t('crud.log.status success'),
                    error: t('crud.log.status error'),
                    start: t('crud.log.status start'),
                },
                custom: { delete: 'danger', success: 'success', error: 'warning', start: '' },
            },
            {
                label: t('crud.log.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: 'start' },
    }
)

provide('baTable', baTable)

const getData = () => {
    baTable.getData()?.then(() => {
        state.ready = true
    })
}

const toggleShowConfig = (status: boolean) => {
    state.showConfig = status
}

const toggleShowBaAccount = (status: boolean) => {
    state.showBaAccount = status
}

const toggleShowUpload = (status: boolean) => {
    state.showUpload = status
}

const toggleShowDownload = (status: boolean) => {
    state.showDownload = status
}

const onLoadLog = (id: string) => {
    crudState.startData.logId = id
    crudState.startData.logType = 'Cloud history'
    changeStep('log')
    emits('update:modelValue', false)
}

const onBatchDelLog = () => {
    let ids: number[] = []
    for (const key in state.downloadSelection) {
        ids.push(state.downloadSelection[key].id)
    }
    onDelLog(ids)
}

const onDelLog = (ids: number[]) => {
    delLog({ ids }).then((res) => {
        uploadCompleted({ syncIds: res.data.syncs, cancelSync: 1 }).finally(() => {
            onLoadLogs()
            baTable.onTableHeaderAction('refresh', {})
        })
    })
}

const onConfigSubmit = () => {
    toggleShowConfig(false)
    config.setCrud('syncType', state.configForm.syncType)
    config.setCrud('syncedUpdate', state.configForm.syncedUpdate)
    config.setCrud('syncAutoPublic', state.configForm.syncAutoPublic)

    ElNotification({
        type: 'success',
        message: t('axios.Operation successful'),
    })
}

const onUpload = () => {
    toggleShowUpload(true)
    state.uploadValidLoading = true
    uploadLog({ logs: baTable.table.selection, save: 0 })
        .then((res) => {
            state.uploadScoreSum = res.data.scoreSum
            state.uploadValidData = res.data.validData
        })
        .finally(() => {
            state.uploadValidLoading = false
        })
}

const onSaveUpload = () => {
    state.uploadValidLoading = true
    const selection = baTable.table.selection
    for (const key in selection) {
        const s = selection[key as keyof typeof selection] as any
        const validDataKey = getArrayKey(state.uploadValidData, 'sync', s.id.toString())
        if (validDataKey !== false) {
            s['public'] = state.uploadValidData[validDataKey].public ? 1 : 0
        }
    }
    uploadLog({ logs: selection, save: 1 }).then((res) => {
        uploadCompleted({ syncIds: res.data.syncIds }).finally(() => {
            baTable.onTableHeaderAction('refresh', {})
            toggleShowUpload(false)
            ElNotification({
                type: 'success',
                message: res.msg,
            })
            state.uploadValidLoading = false
        })
    })
}

const onBaAccountLoginSuccess = () => {
    toggleShowBaAccount(false)
}

const onDownloadCurrentChange = (val: number) => {
    state.downloadPage = val
    onLoadLogs()
}

const onSelectionChange = (newSelection: any[]) => {
    state.downloadSelection = newSelection
}

const onSearchDownloadInput = debounce(() => onLoadLogs(), 500)

const onLoadLogs = () => {
    toggleShowDownload(true)
    state.downloadLoading = true
    logs({ page: state.downloadPage, quickSearch: state.downloadQuickSearch })
        .then((res) => {
            state.downloadData = res.data.list
            state.downloadTotal = res.data.total
        })
        .finally(() => {
            state.downloadLoading = false
        })
}

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal && !state.ready) {
            nextTick(() => {
                getData()
            })
        }
    }
)
</script>

<style lang="scss">
.ba-crud-log-dialog .el-dialog__body {
    padding: 10px 20px;
}
.log-pagination {
    padding: 13px 15px;
}
.cp {
    cursor: pointer;
}
.download-table-header {
    display: flex;
    padding: 10px;
    padding-top: 0;
    .download-table-header-operate-text {
        margin-left: 6px;
        font-size: 14px;
    }
    .download-table-search {
        margin-left: auto;
    }
}
</style>
