<template>
    <Command v-if="state.commandKey" top="230px" right="calc(50% - 230px)" :command-key="state.commandKey" @callback="commandCallback" />
    <Header />
    <div v-if="state.commandKey || state.showMask" class="mask"></div>
    <div class="container">
        <div class="table-title" :class="state.titleClass">{{ state.title }}</div>
        <div v-if="state.showInstallTips" class="install-tips-box">
            <div class="install-tips">
                <img class="install-tips-close" @click="state.showInstallTips = false" src="~assets/img/install/fail.png" alt="关闭手动完成未尽事宜提示" />
                <div class="install-tips-title">
                    安装环境检测并没有完全通过，但安装可以继续，只是您后续需要手动进行一些操作，建议您<span
                        class="change-route"
                        @click="changeRoute('check')"
                        >回到上一页</span
                    >，在所有检测通过后再安装，以便您体验到 BuildAdmin 的核心功能之一。
                </div>
                <div class="install-tips-item">
                    如果你考虑到一些安全因素而不愿开启相应权限，请查看<span class="change-route">安装服务如何保障系统安全</span>
                </div>
                <div class="install-tips-item">
                    如果您确实无法将所有检测调整到通过状态，请<span class="change-route">点击向我们反馈</span
                    >，并继续安装，安装程序后续将引导您，如何手动完成未尽事宜。
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
                        <span>测试连接数据服务器...</span>
                    </div>
                    <div v-show="state.errorPrompt" class="error-prompt">{{ state.errorPrompt }}</div>
                    <div class="button" @click="doneBaseConfig" :class="state.checkDone ? 'pass' : ''">立即安装</div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import Header from '../components/header/index.vue'
import Command from '../components/command/index.vue'
import { testDatabaseUrl, baseConfigUrl, mvDistUrl, envCommandExecutionCheckUrl } from '/@/api/install/index'
import { Axios, errorTips } from '/@/utils/axios'
import { useI18n } from 'vue-i18n'
import { global } from '/@/utils/globalVar'
const { t } = useI18n()

const state: {
    formItem: any
    showFormItem: boolean
    showError: string
    errorPrompt: string
    checkDone: boolean
    databaseCheck: string
    databases: any
    commandKey: string
    title: string
    titleClass: string
    showMask: boolean
    showInstallTips: boolean
} = reactive({
    formItem: {
        hostname: {
            label: 'MySQL 数据库地址',
            value: '127.0.0.1',
            name: 'hostname',
            type: 'text',
        },
        username: {
            label: 'MySQL 连接用户名',
            value: 'root',
            name: 'username',
            type: 'text',
        },
        password: {
            label: 'MySQL 连接密码',
            value: '',
            name: 'password',
            type: 'password',
        },
        hostport: {
            label: 'MySQL 连接端口号',
            value: '3306',
            name: 'hostport',
            type: 'number',
        },
        database: {
            label: 'MySQL 数据库名',
            value: '',
            name: 'database',
            type: 'text',
        },
        prefix: {
            label: 'MySQL 数据表前缀',
            value: 'bd_',
            name: 'prefix',
            type: 'text',
        },
        br1: {
            type: 'br',
        },
        adminname: {
            label: '管理员用户名',
            value: 'admin',
            name: 'adminname',
            type: 'text',
        },
        adminpassword: {
            label: '管理员密码',
            value: '',
            name: 'adminpassword',
            type: 'password',
        },
        repeatadminpassword: {
            label: '重复管理员密码',
            value: '',
            name: 'repeatadminpassword',
            type: 'password',
        },
        br2: {
            type: 'br',
        },
        sitename: {
            label: '站点名称',
            value: 'BuildAdmin',
            name: 'sitename',
            type: 'text',
        },
    },
    showFormItem: false,
    showError: '',
    errorPrompt: '',
    checkDone: false,
    databaseCheck: 'wait',
    databases: [],
    commandKey: '',
    title: '站点配置',
    titleClass: '',
    showMask: false,
    showInstallTips: true,
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

Axios.get(envCommandExecutionCheckUrl)
    .then((res) => {
        console.log(res)
    })
    .catch((err) => {
        console.log(err)
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
                        if (state.errorPrompt == '输入的数据库没有找到!') {
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
                return '输入的数据库没有找到!'
            }
        }
    },
    prefix: function (prefix: string) {
        if (prefix) {
            var reg = new RegExp(/^[a-zA-Z][a-zA-Z0-9_]*$/i)
            if (!reg.test(prefix)) {
                return '表前缀只能包含字母数字和下划线,并以字母开头'
            }
        }
    },
    adminpassword: function (adminpassword: string) {
        if (adminpassword) {
            if (adminpassword.length < 6 || adminpassword.length > 32) {
                return '密码需要在6~32位之间'
            }
        }
    },
    repeatadminpassword: function (adminpassword: string, repeatadminpassword: string) {
        if (adminpassword && repeatadminpassword && adminpassword != repeatadminpassword) {
            return '重复密码不匹配'
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
            state.checkDone = true
        } else {
            state.checkDone = false
        }
    },
}

const doneBaseConfig = () => {
    let values = {}
    for (const key in state.formItem) {
        if (state.formItem[key].value) {
            values = Object.assign(values, {
                [key]: state.formItem[key].value,
            })
        }
    }
    Axios.post(baseConfigUrl, values).then((res) => {
        if (res.data.code == 1) {
            if (res.data.data.execution) {
                state.title = '正在自动执行 WEB端的 cnpm install 命令'
                state.titleClass = 'text-primary'
                state.commandKey = 'web-install'
            } else {
                state.title = '安装完成，请手动完成未尽事宜...'
                state.titleClass = 'text-danger'
                state.showMask = true // 显示遮罩
                state.showInstallTips = false // 显示手动操作安装未尽事宜提示
            }
        } else {
            showGlobalError(res.data.msg)
        }
    })
}

const changeRoute = (routeName: string) => {
    global.step = routeName
}

const commandCallback = (data: any) => {
    if (data.commandKey == 'web-install') {
        if (data.value == 'success') {
            state.title = '正在自动执行 WEB端的 构建命令'
            state.titleClass = 'text-primary'
            state.commandKey = 'web-build'
        } else if (state.commandKey == 'web-install') {
            console.log('web-install-执行失败了')
        }
    } else if (data.commandKey == 'web-build') {
        if (data.value == 'success') {
            state.title = '安装完成...'
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
            console.log('web-build-执行失败了')
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
