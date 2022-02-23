<template>
    <Command v-if="state.commandKey" top="230px" right="calc(50% - 230px)" :command-key="state.commandKey" @callback="commandCallback" />
    <Header />
    <div v-if="state.commandKey || state.showMask" class="mask"></div>
    <div class="container">
        <div class="table-title" :class="state.titleClass">{{ state.title }}</div>
        <div v-if="state.showInstallTips" class="install-tips-box">
            <div class="install-tips">
                <img
                    class="install-tips-close"
                    @click="state.showInstallTips = false"
                    src="~assets/img/install/fail.png"
                    :alt="t('Close the prompt of completing unfinished matters manually')"
                />
                <div class="install-tips-title">
                    {{ t('Install Tips Title 1') }}<span class="change-route" @click="changeRoute('check')">{{ t('Back to previous page') }}</span
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
            <transition name="slide-bottom">
                <div v-show="state.showError" class="table-column table-error">{{ state.showError }}</div>
            </transition>
            <transition-group name="slide-bottom">
                <div v-show="state.showFormItem" v-for="(item, idx) in state.formItem" :key="idx">
                    <div v-if="item.type == 'br'" class="table-item-br"></div>
                    <div v-else class="table-column table-item">
                        <div class="table-label">{{ item.label }}</div>
                        <div class="table-value">
                            <input @change="onFormInputChange" @input="onFormInput" class="table-input" :type="item.type" v-model="item.value" />
                        </div>
                    </div>
                </div>
            </transition-group>
            <transition name="slide-bottom">
                <div v-show="state.showFormItem">
                    <div v-show="state.databaseCheck == 'connecting'" class="error-prompt grey">
                        <span>{{ t('Test connection to data server') }}</span>
                    </div>
                    <div v-show="state.errorPrompt" class="error-prompt">{{ state.errorPrompt }}</div>
                    <div class="button" @click="doneBaseConfig" :class="state.baseConfigSubmitState ? 'pass' : ''">{{ t('Install now') }}</div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted } from 'vue'
import Header from '../components/header/index.vue'
import Command from '../components/command/index.vue'
import { testDatabaseUrl, baseConfigUrl, mvDistUrl } from '/@/api/install/index'
import { Axios, errorTips } from '/@/utils/axios'
import { useI18n } from 'vue-i18n'
import { global } from '/@/utils/globalVar'
const { t } = useI18n()

var timer: NodeJS.Timer

const state: {
    formItem: any
    showFormItem: boolean
    showError: string
    errorPrompt: string
    baseConfigSubmitState: boolean
    databaseCheck: string
    databases: any
    commandKey: string
    title: string
    titleClass: string
    showMask: boolean
    showInstallTips: boolean
    autoJumpSeconds: number
} = reactive({
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
            value: 'bd_',
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
    errorPrompt: '',
    baseConfigSubmitState: false,
    databaseCheck: 'wait',
    databases: [],
    commandKey: '',
    title: t('Site configuration'),
    titleClass: '',
    showMask: false,
    showInstallTips: false,
    autoJumpSeconds: 5,
})

const showGlobalError = (msg: string = '') => {
    if (!msg) {
        if (state.databaseCheck != 'connect-ok' && state.databaseCheck) {
            msg = state.databaseCheck
        }
    }
    state.showError = msg

    validation.done()
}

Axios.get(baseConfigUrl)
    .then((res) => {
        if (res.data.code == 1) {
            state.showInstallTips = !res.data.data.envOk
        } else if (res.data.msg) {
            showGlobalError(res.data.msg)
        } else {
            state.showInstallTips = true
        }
    })
    .catch((err) => {
        showGlobalError(t(errorTips(err)))
    })

const validation = {
    input: function () {
        let m1 = this.repeatadminpassword(state.formItem.adminpassword.value, state.formItem.repeatadminpassword.value)
        let m2 = this.prefix(state.formItem.prefix.value)
        return m1 || m2
    },
    change: function () {
        this.connect()
        let m1 = validation.adminpassword(state.formItem.adminpassword.value)
        let m2 = this.database(state.formItem.database.value)
        let m3 = this.input()
        return m1 || m2 || m3
    },
    connect: function () {
        let database = {
            hostname: state.formItem.hostname.value,
            username: state.formItem.username.value,
            password: state.formItem.password.value,
            hostport: state.formItem.hostport.value,
            database: state.formItem.database.value,
        }
        if (database.hostname && database.username && database.password && database.hostport) {
            state.databaseCheck = state.databaseCheck == 'connect-ok' ? 'wait' : 'connecting'
            Axios.post(testDatabaseUrl, database)
                .then((res) => {
                    if (res.data.code == 1) {
                        state.databaseCheck = 'connect-ok'
                        state.databases = res.data.data.databases
                        showGlobalError()

                        let msg = this.database(state.formItem.database.value)
                        if (state.errorPrompt == t('The entered database was not found!')) {
                            state.errorPrompt = msg ? msg : ''
                        }
                    } else {
                        state.databaseCheck = res.data.msg
                        showGlobalError()
                    }
                })
                .catch((err) => {
                    state.databaseCheck = t(errorTips(err))
                    showGlobalError()
                })
        }
    },
    database: function (database: string) {
        if (database) {
            if (state.databases.indexOf(database) === -1) {
                return t('The entered database was not found!')
            }
        }
    },
    prefix: function (prefix: string) {
        if (prefix) {
            var reg = new RegExp(/^[a-zA-Z][a-zA-Z0-9_]*$/i)
            if (!reg.test(prefix)) {
                return t('The table prefix can only contain alphanumeric characters and underscores, and starts with a letter')
            }
        }
    },
    adminpassword: function (adminpassword: string) {
        if (adminpassword) {
            if (adminpassword.length < 6 || adminpassword.length > 32) {
                return t('The password needs to be between 6 and 32 bits')
            }
        }
    },
    repeatadminpassword: function (adminpassword: string, repeatadminpassword: string) {
        if (adminpassword && repeatadminpassword && adminpassword != repeatadminpassword) {
            return t('Duplicate passwords do not match')
        }
    },
    done: function () {
        if (
            !state.errorPrompt &&
            !state.showError &&
            state.databaseCheck == 'connect-ok' &&
            state.formItem.database.value &&
            state.formItem.prefix.value &&
            state.formItem.adminname.value &&
            state.formItem.adminpassword.value &&
            state.formItem.repeatadminpassword.value &&
            state.formItem.sitename
        ) {
            state.baseConfigSubmitState = true
        } else {
            state.baseConfigSubmitState = false
        }
    },
}

const commandFail = () => {
    state.commandKey = ''
    state.title = t('Command execution failed')
    state.titleClass = 'text-danger'
    state.baseConfigSubmitState = false

    timer = setInterval(() => {
        if (state.autoJumpSeconds <= 0) {
            clearInterval(timer)
            changeRoute('manual-install')
        } else {
            state.autoJumpSeconds--
            showGlobalError(t('Manual Install 1') + t('Manual Install 2', { seconds: state.autoJumpSeconds }))
        }
    }, 1000)
}

const doneBaseConfig = () => {
    state.errorPrompt = t('Installing')
    state.baseConfigSubmitState = false
    let values = {}
    for (const key in state.formItem) {
        if (state.formItem[key].value) {
            values = Object.assign(values, {
                [key]: state.formItem[key].value,
            })
        }
    }
    Axios.post(baseConfigUrl, values)
        .then((res) => {
            state.errorPrompt = ''
            state.baseConfigSubmitState = true
            if (res.data.code == 1) {
                if (res.data.data.execution) {
                    state.title = t("The 'cnpm install' command on the web side is being automatically executed")
                    state.titleClass = 'text-primary'
                    state.commandKey = 'web-install'
                } else {
                    state.title = t('After installation, please complete the unfinished matters manually')
                    state.titleClass = 'text-danger'
                    state.showMask = true // 显示遮罩
                    state.showInstallTips = false // 隐藏手动操作安装未尽事宜提示

                    // 跳转到手动完成未尽事宜页面
                    changeRoute('manual-install')
                }
            } else {
                showGlobalError(res.data.msg)
            }
        })
        .catch((err) => {
            state.errorPrompt = ''
            state.baseConfigSubmitState = true
            showGlobalError(t(errorTips(err)))
        })
}

const changeRoute = (routeName: string) => {
    global.step = routeName
}
const goUrl = (url: string) => {
    window.open(url)
}

const commandCallback = (data: any) => {
    if (data.commandKey == 'web-install') {
        if (data.value == 'success') {
            state.title = t('Automatically executing the build command on the web side')
            state.titleClass = 'text-primary'
            state.commandKey = 'web-build'
        } else if (state.commandKey == 'web-install') {
            commandFail()
        }
    } else if (data.commandKey == 'web-build') {
        if (data.value == 'success') {
            state.title = t('Installation complete')
            state.titleClass = 'text-primary'
            Axios.post(mvDistUrl)
                .then((res) => {
                    if (res.data.code == 1) {
                        changeRoute('done')
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        } else if (state.commandKey == 'web-build') {
            commandFail()
        }
        state.commandKey = ''
    }
}

const onFormInputChange = () => {
    let msg = validation.change()
    state.errorPrompt = msg ? msg : ''

    validation.done()
}
const onFormInput = () => {
    let msg = validation.input()
    state.errorPrompt = msg ? msg : ''

    validation.done()
}
onMounted(() => {
    state.showFormItem = true
})
onUnmounted(() => {
    clearInterval(timer)
})
</script>

<style scoped lang="scss">
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
.container {
    margin-top: 10px;
    .table-title {
        display: block;
        position: relative;
        text-align: center;
        font-size: 20px;
        color: #303133;
        z-index: 999;
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
                flex: 4;
                font-size: 15px;
                width: 180px;
                padding-right: 20px;
                text-align: right;
            }
        }
        .table-value {
            font-size: 13px;
            display: flex;
            flex: 8;
            align-items: center;
            .table-input {
                background: #fff;
                border: none;
                color: #909399;
                outline: none;
                height: 20px;
                width: 100%;
                font-size: 15px;
                transition: all 0.3s ease;
            }
        }
    }
}
.button {
    padding: 15px;
    text-align: center;
    font-size: 16px;
    background-color: #3f6ad8;
    border-radius: 5px;
    color: #fff;
    margin-top: 20px;
    opacity: 0.4;
    cursor: pointer;
    transition: all 0.2s ease;
}
.button.pass {
    opacity: 1;
}
.error-prompt {
    font-size: 14px;
    margin-top: 20px;
    text-align: right;
    color: #f56c6c;
}
.error-prompt.grey {
    color: #606266;
}
.mask {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 99;
    background-color: rgba($color: #000000, $alpha: 0.4);
}
.text-primary {
    color: #409eff !important;
}
.text-danger {
    color: #f56c6c !important;
}
</style>
