<template>
    <Command v-if="state.command" top="50px" right="20px" @callback="commandCallback" :command-key="state.command" />
    <Header />
    <div class="container">
        <div class="table-title">{{ t('Environmental inspection') }}</div>
        <div class="table">
            <transition-group name="slide-bottom">
                <div class="table-item" :class="idx" v-for="(item, idx) in state.envCheckData" :key="idx + item.describe + item.state">
                    <div class="table-label">
                        {{ t(idx) }}
                        <span
                            v-if="item.need && item.click"
                            :title="item.click.title"
                            @click="onLabelNeed(item.click.type, item.click.url)"
                            class="label-need"
                            >{{ item.need }}</span
                        >
                    </div>
                    <div class="table-value">
                        {{ item.describe }}
                        <img
                            v-if="idx.toString() != 'error' && idx.toString() != 'success'"
                            :title="t(state.stateTitle[item.state])"
                            class="data-state"
                            :src="getSrc(item.state)"
                            :alt="item.state"
                        />
                        <img
                            v-if="idx.toString() == 'error' || idx.toString() == 'success'"
                            @click="closeTableLabel(idx.toString())"
                            class="data-state"
                            src="~assets/img/install/close.png"
                            :alt="item.state"
                        />
                    </div>
                </div>
            </transition-group>
            <div v-if="state.checkTypeIndex != 'done'" class="table-item">
                <div class="table-label">{{ t('Checking installation environment') }}</div>
                <div class="table-value">
                    {{ t(state.checkType[state.checkTypeIndex]) }}
                    <img
                        :title="t('Current execution to:') + t(state.checkType[state.checkTypeIndex])"
                        class="data-state"
                        src="~assets/img/install/loading.gif"
                        :alt="t(state.checkType[state.checkTypeIndex])"
                    />
                </div>
            </div>
            <div v-if="state.showNmpInstall" class="table-item">
                <div class="table-label">{{ t('Installing cnpm') }}</div>
                <div class="table-value">
                    <img class="data-state" src="~assets/img/install/loading.gif" />
                </div>
            </div>
            <div class="check-done" :class="state.checkDoneIndex">{{ t(state.checkDone[state.checkDoneIndex]) }}</div>
            <div class="button" @click="doneCheck" :class="state.checkDoneIndex == 'ok' ? 'pass' : ''">{{ t('Step 2 site configuration') }}</div>
            <img :src="getSrc('ok')" width="0" height="0" alt="" />
            <img :src="getSrc('fail')" width="0" height="0" alt="" />
            <img :src="getSrc('warn')" width="0" height="0" alt="" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import Header from '../components/header/index.vue'
import Command from '../components/command/index.vue'
import axios from 'axios'
import { Axios, errorTips, Response } from '/@/utils/axios'
import { envBaseCheckUrl, envNpmCheckUrl } from '/@/api/install/index'
import { useI18n } from 'vue-i18n'
import { global } from '/@/utils/globalVar'

const { t, locale } = useI18n()
var langValue = window.localStorage.getItem('bd-lang') || 'zh-cn'
locale.value = langValue

const modules = import.meta.globEager('../assets/img/install/*.png')

const state: {
    envCheckData: any
    stateTitle: any
    checkType: any
    checkTypeIndex: string
    checkDone: any
    checkDoneIndex: string
    showNmpInstall: boolean
    command: string
} = reactive({
    envCheckData: [],
    stateTitle: {
        ok: 'Environmental inspection passed',
        fail: 'This environmental check failed',
        warn: 'The environment check failed, but the installation can continue',
    },
    checkType: {
        base: 'Basic environment',
        npm: 'NPM correlation',
        npminstall: 'Test cnpm install',
        done: 'Check complete',
    },
    checkTypeIndex: 'base',
    checkDone: {
        ok: 'Congratulations, the installation can continue~',
        fail: 'Sorry, the necessary installation environment conditions have not been met, please check the above form!',
        executing: 'executing',
    },
    checkDoneIndex: 'executing',
    showNmpInstall: false,
    command: '',
})

const doneCheck = () => {
    if (state.checkDoneIndex == 'ok') {
        global.step = 'base-config'
    }
}

const getSrc = (name: string) => {
    const path = `../assets/img/install/${name}.png`
    return modules[path].default
}

const onLabelNeed = (type: string, url: string) => {
    if (type == 'faq') {
        window.open(url)
    } else if (type == 'install-cnpm') {
        state.checkDoneIndex = 'executing'
        state.showNmpInstall = true
        state.command = 'install-cnpm'
    } else if (type == 'test-cnpm-install') {
        axiosNpmInstall()
    }
}

const commandCallback = (data: any) => {
    if (data.commandKey == 'test-install') {
        state.checkTypeIndex = 'done'
        checkDoneCallBack()
        if (data.value == 'success') {
            let cnpmInstall = { 'cnpm-install': { describe: t('Can execute'), state: 'ok', need: '', click: { title: '', type: '', url: '' } } }
            if (!state.envCheckData['cnpm-install']) {
                state.envCheckData = Object.assign({}, state.envCheckData, cnpmInstall)
            }
        } else {
            let cnpmInstall = {
                'cnpm-install': {
                    describe: t('Command execution test failed'),
                    state: 'warn',
                    need: t('The installation can continue, and some operations need to be completed manually'),
                    click: { title: t('Click to see how to solve it'), type: 'faq', url: 'https://baidu.com?wd=cnpm install执行失败' },
                },
            }
            if (!state.envCheckData['cnpm-install']) {
                state.envCheckData = Object.assign({}, state.envCheckData, cnpmInstall)
            }
        }
    } else if (data.commandKey == 'install-cnpm') {
        if (data.value == 'success') {
            state.envCheckData = Object.assign({}, state.envCheckData, {
                success: {
                    describe: t('Cnpm is ready!'),
                    state: 'ok',
                },
            })
            state.envCheckData['cnpm_version'] = {
                describe: t('already installed'),
                state: 'ok',
                need: '',
            }
            addCheckCnpmInstall()
        } else if (state.showNmpInstall) {
            // 意味着completed到达了，但是success没有到达
            catchErr(t('Sorry, the automatic installation of cnpm failed. Please complete the installation of cnpm manually!'))
        }
        state.showNmpInstall = false
        checkDoneCallBack()
    }
}

const closeTableLabel = (idx: string) => {
    delete state.envCheckData[idx]
}

const catchErr = (err: string) => {
    state.envCheckData = Object.assign({}, state.envCheckData, {
        error: {
            describe: err,
            state: 'fail',
        },
    })
}

const axiosThen = function (data: Response, thenCallback: Function = () => {}) {
    if (data.code == 1) {
        state.envCheckData = Object.assign({}, state.envCheckData, data.data)
        thenCallback(data)
    } else if (data.code == 3) {
        catchErr(data.msg)
        state.checkDoneIndex = 'fail'
        state.checkTypeIndex = 'done'
    } else if (data.code == 2) {
    } else {
        catchErr(data.msg)
    }
}

const checkDoneCallBack = () => {
    state.checkTypeIndex = 'done'
    if (
        state.envCheckData['php_version'] &&
        state.envCheckData['php_version']['state'] == 'ok' &&
        state.envCheckData['config_is_writable']['state'] &&
        state.envCheckData['public_is_writable']['state']
    ) {
        state.checkDoneIndex = 'ok'
    } else {
        state.checkDoneIndex = 'fail'
    }
}

const axiosNpmInstall = () => {
    state.checkDoneIndex = 'executing'
    closeTableLabel('check cnpm install')
    state.checkTypeIndex = 'npminstall'
    state.command = 'test-install'
}

// 点击测试cnpm install
const addCheckCnpmInstall = () => {
    state.envCheckData = Object.assign({}, state.envCheckData, {
        'check cnpm install': {
            describe: '',
            state: 'warn',
            need: t('Click to test'),
            click: {
                title: t('Click to test') + ' cnpm install',
                type: 'test-cnpm-install',
                url: '',
            },
        },
    })
}

const getEnvBaseCheckUrl = () => {
    return Axios.get(envBaseCheckUrl)
        .then((res) => {
            axiosThen(res.data, () => {
                state.checkTypeIndex = 'npm'
            })
        })
        .catch((err) => {
            catchErr(t(errorTips(err)))
        })
}

const getEnvNpmCheckUrl = () => {
    return Axios.get(envNpmCheckUrl)
        .then((res) => {
            axiosThen(res.data, (data: Response) => {
                if (data.data.cnpm_version.state == 'ok') {
                    // axiosNpmInstall()
                    state.checkTypeIndex = 'done'
                    addCheckCnpmInstall()
                }
            })
        })
        .catch((err) => {
            catchErr(t(errorTips(err)))
        })
}

onMounted(() => {
    axios.all([getEnvBaseCheckUrl(), getEnvNpmCheckUrl()]).then(() => {
        checkDoneCallBack()
    })
})
</script>

<style scoped lang="scss">
.container {
    margin-top: 20px;
    .table-title {
        display: block;
        text-align: center;
        font-size: 20px;
        color: #303133;
    }
    .table {
        max-width: 500px;
        padding: 20px;
        margin: 10px auto;
    }
    .table-item {
        color: #303133;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #fff;
        padding: 13px 15px;
        margin-bottom: 2px;
        border-radius: 5px;
        transition: all 0.2s ease;
        &:hover {
            background-color: #fcfcfc;
        }
        .table-label {
            font-size: 15px;
        }
        .label-need {
            font-size: 12px;
            color: #f56c6c;
            cursor: pointer;
        }
    }
    .table-item.error {
        background-color: #f56c6c;
        color: #fff;
    }
    .table-item.success {
        background-color: #67c23a;
        color: #fff;
    }
    .table-value {
        font-size: 13px;
        display: flex;
        align-items: center;
    }
    .data-state {
        width: 20px;
        height: 20px;
        user-select: none;
        margin-left: 5px;
    }
}
.check-done {
    font-size: 14px;
    margin-top: 20px;
    text-align: right;
}
.check-done.ok {
    color: #67c23a;
}
.check-done.fail {
    color: #f56c6c;
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
</style>
