<template>
    <Header />
    <div class="container">
        <div class="table-title">{{ t('Site configuration') }}</div>
        <div v-if="state.showInstallTips" class="install-tips-box">
            <div class="install-tips">
                <img
                    class="install-tips-close"
                    @click="state.showInstallTips = false"
                    src="~assets/img/install/fail.png"
                    :alt="t('Close the prompt of completing unfinished matters manually')"
                />
                <div class="install-tips-title">
                    {{ t('Install Tips Title 1') }}<span class="change-route" @click="common.setStep('check')">{{ t('Back to previous page') }}</span
                    >{{ t('Install Tips Title 2') }}
                </div>
                <div class="install-tips-item">
                    {{ t("If you don't want to open the corresponding permission due to some security factors, please check ")
                    }}<span @click="goUrl('https://www.kancloud.cn/buildadmin/buildadmin/2653889')" class="change-route">{{
                        t('how installation services ensure system security')
                    }}</span>
                </div>
                <div class="install-tips-item">
                    {{ t("If you really can't adjust all the tests to pass, please ")
                    }}<span class="change-route">{{ t('click to feed back to us') }}</span
                    >{{
                        t(
                            ' and continue the installation. The subsequent installation program will guide you on how to manually complete the outstanding matters.'
                        )
                    }}
                </div>
            </div>
        </div>
        <div class="table">
            <el-form ref="formRef" label-width="150px" @keyup.enter="submitBaseConfig(formRef)" :rules="rules" :model="state.formItem">
                <transition name="slide-bottom">
                    <div v-show="state.showError" class="table-column table-error">{{ state.showError }}</div>
                </transition>
                <transition-group name="slide-bottom">
                    <div v-show="state.showFormItem" v-for="(item, idx) in state.formItem" :key="idx">
                        <div v-if="item.type == 'br'" class="table-item-br"></div>
                        <div v-else class="table-column table-item">
                            <el-form-item :prop="item.name" class="table-label" :label="item.label">
                                <el-input v-model="item.value" class="table-input" :type="item.type"></el-input>
                            </el-form-item>
                        </div>
                    </div>
                </transition-group>
                <transition name="slide-bottom">
                    <div v-show="state.showFormItem">
                        <div v-show="state.databaseCheck == 'connecting'" class="connecting-prompt">
                            <span>{{ t('Test connection to data server') }}</span>
                        </div>
                        <el-button type="primary" class="button" @click="submitBaseConfig(formRef)" :loading="state.baseConfigSubmitState">{{
                            t('Install now')
                        }}</el-button>
                    </div>
                </transition>
            </el-form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted, ref } from 'vue'
import Header from '../components/header/index.vue'
import { postTestDatabase, getBaseConfig, postBaseConfig, commandExecComplete } from '/@/api/install/index'
import { useI18n } from 'vue-i18n'
import { ConfigState, DatabaseData } from '/@/stores/interface/index'
import { useCommon } from '/@/stores/common'
import { useTerminal } from '/@/stores/terminal'
import { ElForm, ElMessage, FormItemRule } from 'element-plus'
import { taskStatus } from '/@/components/terminal/constant'

var timer: NodeJS.Timer
var databaseData: Partial<DatabaseData> = { hostname: '', username: '', password: '', hostport: '' }
const { t } = useI18n()
const common = useCommon()
const terminal = useTerminal()
const formRef = ref<InstanceType<typeof ElForm>>()

const state: ConfigState = reactive({
    formItem: {
        hostname: {
            label: t('Mysql database address'),
            value: '127.0.0.1',
            name: 'hostname',
            type: 'text',
        },
        username: {
            label: t('MySQL connection user name'),
            value: 'root',
            name: 'username',
            type: 'text',
        },
        password: {
            label: t('MySQL connection password'),
            value: '',
            name: 'password',
            type: 'password',
        },
        hostport: {
            label: t('MySQL connection port number'),
            value: '3306',
            name: 'hostport',
            type: 'number',
        },
        database: {
            label: t('Mysql database name'),
            value: '',
            name: 'database',
            type: 'text',
        },
        prefix: {
            label: t('MySQL data table prefix'),
            value: 'ba_',
            name: 'prefix',
            type: 'text',
        },
        br1: {
            type: 'br',
        },
        adminname: {
            label: t('Administrator user name'),
            value: 'admin',
            name: 'adminname',
            type: 'text',
        },
        adminpassword: {
            label: t('Administrator password'),
            value: '',
            name: 'adminpassword',
            type: 'password',
        },
        repeatadminpassword: {
            label: t('Duplicate administrator password'),
            value: '',
            name: 'repeatadminpassword',
            type: 'password',
        },
        br2: {
            type: 'br',
        },
        sitename: {
            label: t('Site name'),
            value: 'BuildAdmin',
            name: 'sitename',
            type: 'text',
        },
    },
    showFormItem: false,
    showError: '',
    baseConfigSubmitState: false,
    databaseCheck: 'wait',
    databases: [],
    showInstallTips: false,
    autoJumpSeconds: 5,
})

const validation = {
    required: (rule: any, field: { value: string; label: string }, callback: any) => {
        if (field.value == '' || !field.value) {
            return callback(new Error(field.label + t('Required')))
        }
        return callback()
    },
    findDatabase: (database: string, showError: boolean, callback: any = null) => {
        if (state.databaseCheck != 'connect-ok') {
            if (callback != null) {
                return callback()
            }
            return
        }
        if (database) {
            if (state.databases.indexOf(database) === -1) {
                if (showError) {
                    ElMessage({
                        type: 'error',
                        message: t('The entered database was not found!'),
                        center: true,
                    })
                } else {
                    return callback(t('The entered database was not found!'))
                }
            }
        }
        if (callback != null) {
            return callback()
        }
    },
    database: (rule: any, field: { value: string; label: string }, callback: any) => {
        validation.findDatabase(field.value, false, callback)
    },
    connect: (rule: any, field: { value: string; label: string }, callback: any) => {
        let flag = false
        for (const key in databaseData) {
            if (databaseData[key as keyof DatabaseData] != state.formItem[key].value) {
                flag = true
            }
        }
        if (!flag) {
            return callback()
        }
        databaseData = {
            hostname: state.formItem.hostname.value,
            username: state.formItem.username.value,
            password: state.formItem.password.value,
            hostport: state.formItem.hostport.value,
            database: state.formItem.database.value,
        }
        if (databaseData.hostname && databaseData.username && databaseData.password && databaseData.hostport) {
            state.databaseCheck = 'connecting'
            postTestDatabase(databaseData).then((res) => {
                if (res.data.code == 1) {
                    state.databaseCheck = 'connect-ok'
                    state.databases = res.data.data.databases
                    if (state.formItem.database.value) validation.findDatabase(state.formItem.database.value, true, null)
                } else {
                    state.databaseCheck = 'wait'
                    state.databases = []
                    ElMessage({
                        type: 'error',
                        message: res.data.msg,
                        center: true,
                    })
                }
            })
        }
        return callback()
    },
    prefix: function (rule: any, field: { value: string; label: string }, callback: any) {
        if (field.value) {
            var reg = new RegExp(/^[a-zA-Z][a-zA-Z0-9_]*$/i)
            if (!reg.test(field.value)) {
                return callback(new Error(t('The table prefix can only contain alphanumeric characters and underscores, and starts with a letter')))
            }
        }
        return callback()
    },
    adminname: function (rule: any, field: { value: string; label: string }, callback: any) {
        if (!/^[a-zA-Z][a-zA-Z0-9_]{2,15}$/.test(field.value)) {
            return callback(new Error(t('It is composed of letters, numbers and underscores, starting with letters (3-15 digits)')))
        }
        return callback()
    },
    adminpassword: function (rule: any, field: { value: string; label: string }, callback: any) {
        if (!/^[a-zA-Z0-9_]{6,32}$/.test(field.value)) {
            return callback(new Error(t('Composed of letters, numbers and underscores, (6-32 bits)')))
        }
        return callback()
    },
    repeatadminpassword: function (rule: any, field: { value: string; label: string }, callback: any) {
        if (state.formItem.adminpassword.value && field.value && state.formItem.adminpassword.value != field.value) {
            return callback(new Error(t('Duplicate passwords do not match')))
        }
        return callback()
    },
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    hostname: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.connect, trigger: 'blur' },
    ],
    username: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.connect, trigger: 'blur' },
    ],
    password: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.connect, trigger: 'blur' },
    ],
    hostport: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.connect, trigger: 'blur' },
    ],
    database: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.database, trigger: 'blur' },
    ],
    prefix: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.prefix, trigger: 'blur' },
    ],
    adminname: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.adminname, trigger: 'blur' },
    ],
    adminpassword: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.adminpassword, trigger: 'blur' },
    ],
    repeatadminpassword: [
        { validator: validation.required, trigger: 'blur' },
        { validator: validation.repeatadminpassword, trigger: 'blur' },
    ],
    sitename: [{ validator: validation.required, trigger: 'blur' }],
})

const goUrl = (url: string) => {
    window.open(url)
}

const setGlobalError = (msg: string = '') => {
    state.showError = msg
}

const execCommand = () => {
    terminal.toggle(true)
    terminal.addTaskPM('web-install', true, (res: number) => {
        if (res == taskStatus.Success) {
            terminal.addTaskPM('web-build', true, (res: number) => {
                if (res == taskStatus.Success) {
                    commandExecComplete()
                    terminal.toggle(false)
                    common.setStep('done')
                } else if (res == taskStatus.Failed) {
                    commandFail()
                }
            })
        } else if (res == taskStatus.Failed) {
            commandFail()
        }
    })
}

const submitBaseConfig = (formEl: InstanceType<typeof ElForm> | undefined = undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            state.baseConfigSubmitState = true
            let values = {}
            for (const key in state.formItem) {
                if (state.formItem[key].value) {
                    values = Object.assign(values, {
                        [key]: state.formItem[key].value,
                    })
                }
            }

            postBaseConfig(values)
                .then((res) => {
                    if (res.data.code == 1) {
                        if (res.data.data.execution) {
                            execCommand()
                        } else {
                            state.showInstallTips = false // 隐藏手动操作安装未尽事宜提示
                            common.setStep('manualInstall') // 跳转到手动完成未尽事宜页面
                        }
                    } else {
                        ElMessage({
                            type: 'error',
                            message: res.data.msg,
                            center: true,
                        })
                    }
                })
                .finally(() => {
                    state.baseConfigSubmitState = false
                })
        }
    })
}

getBaseConfig().then((res) => {
    if (res.data.code == 1) {
        state.showInstallTips = !res.data.data.envOk
    } else if (res.data.code == 0) {
        ElMessage({
            type: 'error',
            message: res.data.msg,
            center: true,
            duration: 0,
        })
    } else if (res.data.code == 302) {
        // 安装配置完成但命令未完成执行
        execCommand()
    } else {
        state.showInstallTips = true
    }
})

const commandFail = () => {
    terminal.toggle(false)
    timer = setInterval(() => {
        if (state.autoJumpSeconds <= 0) {
            clearInterval(timer)
            common.setStep('manualInstall')
        } else {
            state.autoJumpSeconds--
            setGlobalError(t('Manual Install 1') + t('Manual Install 2', { seconds: state.autoJumpSeconds }))
        }
    }, 1000)
}

onMounted(() => {
    state.showFormItem = true
})
onUnmounted(() => {
    clearInterval(timer)
})
</script>

<style scoped lang="scss">
.container {
    margin-top: 10px;
    .table-title {
        display: block;
        text-align: center;
        font-size: 20px;
        color: #303133;
    }
    .table {
        max-width: 500px;
        padding: 20px;
        margin: 0 auto;
        .table-item-br {
            height: 20px;
        }
        .table-item:focus-within {
            .table-input {
                color: #303133;
            }
            border: 2px solid #4e73df;
        }
        .table-column {
            padding: 12px;
            border-radius: 3px;
            border: 2px solid #fff;
            transition: all 0.3s ease;
        }
        .table-error {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background-color: #f56c6c;
            color: #fff;
        }
        .table-item {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
            background-color: #fff;
            color: #909399;
            .table-label {
                flex: 1;
                font-size: 15px;
                margin-bottom: 0;
            }
        }
    }
    .button {
        margin-top: 20px;
        width: 100%;
        height: 42px;
    }
    .connecting-prompt {
        position: fixed;
        top: 60px;
        right: 100px;
        font-size: 14px;
        margin-top: 20px;
        text-align: right;
        color: #606266;
    }
    :deep(.el-input__wrapper) {
        box-shadow: none;
    }
    :deep(.el-input__wrapper.is-focus) {
        box-shadow: none;
    }
    :deep(.el-form-item.is-error .el-input__wrapper) {
        box-shadow: none;
    }
    :deep(.el-form-item__error) {
        left: 11px;
        margin-top: -6px;
    }
    :deep(.el-input__inner) {
        line-height: 29px;
    }
}
.install-tips-box {
    padding: 0 20px;
    .install-tips-close {
        position: absolute;
        width: 22px;
        height: 22px;
        top: -11px;
        right: -11px;
        border: 1px solid #d50600;
        border-radius: 50%;
    }
    .install-tips {
        position: relative;
        padding: 10px;
        background-color: #ffcdcd;
        color: #d50600;
        max-width: 570px;
        margin: 20px auto 0 auto;
        border-radius: 4px;
        font-size: 14px;
        .install-tips-title,
        .install-tips-item {
            text-indent: 1em;
            background-color: #ffe5e5;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .install-tips-item:last-child {
            margin-bottom: 0;
        }
    }
    .change-route {
        cursor: pointer;
        color: #3f6ad8;
    }
}
</style>
