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
                        <template v-if="item.link && item.link.length > 0">
                            <span
                                v-for="(link, linkidx) in item.link"
                                :key="linkidx"
                                :title="link.title ? link.title : ''"
                                @click="onLabelNeed(link)"
                                class="label-need"
                                :class="link.type"
                                >{{ link.name }}</span
                            >
                        </template>
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

interface Link {
    name: string
    type: string
    title?: string
    url?: string
}
const onLabelNeed = (link: Link) => {
    if (link.type == 'faq') {
        window.open(link.url)
    } else if (link.type == 'install-cnpm') {
        // 改为继续检查
        state.checkDoneIndex = 'executing'

        // 正在执行npm install
        state.showNmpInstall = true
        state.command = 'install-cnpm'
    } else if (link.type == 'test-cnpm-install') {
        axiosNpmInstall()
    }
}

const commandCallback = (data: any) => {
    if (data.commandKey == 'test-install') {
        state.checkTypeIndex = 'done' // 结束检查loading
        checkSubmit() // 检查是否可提交
        if (data.value == 'success') {
            // test-install 执行成功
            let cnpmInstall = { 'cnpm-install': { describe: t('Can execute'), state: 'ok', link: [] } }
            if (!state.envCheckData['cnpm-install']) {
                state.envCheckData = Object.assign({}, state.envCheckData, cnpmInstall)
            }
        } else {
            // test-install 执行失败
            let cnpmInstall = {
                'cnpm-install': {
                    describe: t('Command execution test failed'),
                    state: 'warn',
                    link: [
                        {
                            // 如何解决
                            name: t('How to solve'),
                            title: t('Click to see how to solve it'),
                            type: 'faq',
                            url: 'https://www.kancloud.cn/buildadmin/buildadmin/2653900',
                        },
                    ],
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
                    link: [],
                },
            })
            state.envCheckData['cnpm_version'] = {
                describe: t('already installed'),
                state: 'ok',
                link: [],
            }
            addCheckCnpmInstall()
        } else if (state.showNmpInstall) {
            // 意味着completed到达了，但是success没有到达
            catchErr(t('Sorry, the automatic installation of cnpm failed. Please complete the installation of cnpm manually!'))
        }
        state.showNmpInstall = false
        checkSubmit()
    }
}

// 关闭table的某行
const closeTableLabel = (idx: string) => {
    delete state.envCheckData[idx]
}

const catchErr = (err: string) => {
    state.envCheckData = Object.assign({}, state.envCheckData, {
        error: {
            describe: err,
            state: 'fail',
            link: [],
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

const checkSubmit = () => {
    state.checkTypeIndex = 'done'
    if (
        state.envCheckData['php_version'] &&
        state.envCheckData['php_version']['state'] == 'ok' &&
        state.envCheckData['config_is_writable']['state'] &&
        state.envCheckData['public_is_writable']['state'] &&
        state.envCheckData['php-mysqli']['state'] == 'ok'
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
            link: [
                {
                    name: t('Click to test'),
                    title: t('Click to test') + ' cnpm install',
                    type: 'test-cnpm-install',
                },
            ],
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
        checkSubmit()
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
            padding: 0 4px;
        }
        .label-need.faq,
        .label-need.install-cnpm {
            color: #3f6ad8;
            &:hover {
                text-decoration: underline;
            }
        }
        .label-need.text {
            cursor: text;
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
