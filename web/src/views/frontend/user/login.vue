<template>
    <div class="login">
        <el-container class="is-vertical">
            <Header />
            <el-main class="frontend-footer-brother">
                <el-row justify="center">
                    <el-col :span="16" :xs="24">
                        <div v-if="memberCenter.state.open" class="login-box">
                            <div class="login-title">
                                {{ t('user.login.' + state.form.tab) + t('user.login.reach') + siteConfig.siteName }}
                            </div>
                            <el-form ref="formRef" @keyup.enter="onSubmitPre" :rules="rules" :model="state.form">
                                <!-- 注册验证方式 -->
                                <el-form-item v-if="state.form.tab == 'register'">
                                    <el-radio-group size="large" v-model="state.form.registerType">
                                        <el-radio
                                            class="register-verification-radio"
                                            value="email"
                                            :disabled="!state.accountVerificationType.includes('email')"
                                            border
                                        >
                                            {{ t('user.login.Via email') + t('user.login.register') }}
                                        </el-radio>
                                        <el-radio
                                            class="register-verification-radio"
                                            value="mobile"
                                            :disabled="!state.accountVerificationType.includes('mobile')"
                                            border
                                        >
                                            {{ t('user.login.Via mobile number') + t('user.login.register') }}
                                        </el-radio>
                                    </el-radio-group>
                                </el-form-item>

                                <!-- 登录注册用户名 -->
                                <el-form-item prop="username">
                                    <el-input
                                        v-model="state.form.username"
                                        :placeholder="
                                            state.form.tab == 'register'
                                                ? t('Please input field', { field: t('user.login.User name') })
                                                : t('Please input field', { field: t('user.login.account') })
                                        "
                                        :clearable="true"
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-user" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <!-- 登录注册密码 -->
                                <el-form-item prop="password">
                                    <el-input
                                        v-model="state.form.password"
                                        :placeholder="t('Please input field', { field: t('user.login.password') })"
                                        type="password"
                                        show-password
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <!-- 注册手机号 -->
                                <el-form-item v-if="state.form.tab == 'register' && state.form.registerType == 'mobile'" prop="mobile">
                                    <el-input
                                        v-model="state.form.mobile"
                                        :placeholder="t('Please input field', { field: t('user.login.mobile') })"
                                        :clearable="true"
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-tablet" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <!-- 注册邮箱 -->
                                <el-form-item v-if="state.form.tab == 'register' && state.form.registerType == 'email'" prop="email">
                                    <el-input
                                        v-model="state.form.email"
                                        :placeholder="t('Please input field', { field: t('user.login.email') })"
                                        :clearable="true"
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-envelope" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <!-- 注册验证码 -->
                                <el-form-item v-if="state.form.tab == 'register'" prop="captcha">
                                    <el-row class="w100">
                                        <el-col :span="16">
                                            <el-input
                                                size="large"
                                                v-model="state.form.captcha"
                                                :placeholder="t('Please input field', { field: t('user.login.Verification Code') })"
                                                autocomplete="off"
                                            >
                                                <template #prefix>
                                                    <Icon name="fa fa-ellipsis-h" size="16" color="var(--el-input-icon-color)" />
                                                </template>
                                            </el-input>
                                        </el-col>
                                        <el-col class="captcha-box" :span="8">
                                            <el-button
                                                size="large"
                                                @click="sendRegisterCaptchaPre"
                                                :loading="state.sendCaptchaLoading"
                                                :disabled="state.codeSendCountdown <= 0 ? false : true"
                                                type="primary"
                                            >
                                                {{
                                                    state.codeSendCountdown <= 0
                                                        ? t('user.login.send')
                                                        : state.codeSendCountdown + t('user.login.seconds')
                                                }}
                                            </el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>

                                <div v-if="state.form.tab != 'register'" class="form-footer">
                                    <el-checkbox v-model="state.form.keep" :label="t('user.login.Remember me')" size="default"></el-checkbox>
                                    <div
                                        v-if="state.accountVerificationType.length > 0"
                                        @click="state.showRetrievePasswordDialog = true"
                                        class="forgot-password"
                                    >
                                        {{ t('user.login.Forgot your password?') }}
                                    </div>
                                </div>
                                <el-form-item class="form-buttons">
                                    <el-button class="login-btn" @click="onSubmitPre" :loading="state.formLoading" round type="primary" size="large">
                                        {{ t('user.login.' + state.form.tab) }}
                                    </el-button>
                                    <el-button
                                        v-if="state.form.tab == 'register'"
                                        @click="switchTab(formRef, 'login')"
                                        round
                                        plain
                                        type="info"
                                        size="large"
                                    >
                                        {{ t('user.login.Back to login') }}
                                    </el-button>
                                    <el-button v-else @click="switchTab(formRef, 'register')" round plain type="info" size="large">
                                        {{ t('user.login.No account yet? Click Register') }}
                                    </el-button>
                                </el-form-item>
                                <LoginFooterMixin />
                            </el-form>
                        </div>
                        <el-alert v-else :center="true" :title="$t('Member center disabled')" type="error" />
                    </el-col>
                </el-row>
            </el-main>
            <Footer />
        </el-container>

        <el-dialog
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            v-model="state.showRetrievePasswordDialog"
            :title="t('user.login.Retrieve password')"
            :width="state.dialogWidth + '%'"
            :draggable="true"
        >
            <div class="retrieve-password-form">
                <el-form
                    ref="retrieveFormRef"
                    @keyup.enter="onSubmitRetrieve()"
                    :rules="retrieveRules"
                    :model="state.retrievePasswordForm"
                    :label-width="100"
                >
                    <el-form-item :label="t('user.login.Retrieval method')">
                        <el-radio-group v-model="state.retrievePasswordForm.type">
                            <el-radio value="email" :disabled="!state.accountVerificationType.includes('email')" border>
                                {{ t('user.login.Via email') }}
                            </el-radio>
                            <el-radio value="mobile" :disabled="!state.accountVerificationType.includes('mobile')" border>
                                {{ t('user.login.Via mobile number') }}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item prop="account" :label="state.retrievePasswordForm.type == 'email' ? t('user.login.email') : t('user.login.mobile')">
                        <el-input
                            v-model="state.retrievePasswordForm.account"
                            :placeholder="
                                t('Please input field', {
                                    field: state.retrievePasswordForm.type == 'email' ? t('user.login.email') : t('user.login.mobile'),
                                })
                            "
                            :clearable="true"
                        >
                            <template #prefix>
                                <Icon name="fa fa-user" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="captcha" :label="t('user.login.Verification Code')">
                        <el-row class="w100">
                            <el-col :span="16">
                                <el-input
                                    v-model="state.retrievePasswordForm.captcha"
                                    :placeholder="t('Please input field', { field: t('user.login.Verification Code') })"
                                    autocomplete="off"
                                >
                                    <template #prefix>
                                        <Icon name="fa fa-ellipsis-h" size="16" color="var(--el-input-icon-color)" />
                                    </template>
                                </el-input>
                            </el-col>
                            <el-col class="captcha-box" :span="8">
                                <el-button
                                    @click="sendRetrieveCaptchaPre"
                                    :loading="state.sendCaptchaLoading"
                                    :disabled="state.codeSendCountdown <= 0 ? false : true"
                                    type="primary"
                                >
                                    {{ state.codeSendCountdown <= 0 ? t('user.login.send') : state.codeSendCountdown + t('user.login.seconds') }}
                                </el-button>
                            </el-col>
                        </el-row>
                    </el-form-item>
                    <el-form-item prop="password" :label="t('user.login.New password')">
                        <el-input
                            v-model="state.retrievePasswordForm.password"
                            :placeholder="t('Please input field', { field: t('user.login.New password') })"
                            show-password
                        >
                            <template #prefix>
                                <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="state.showRetrievePasswordDialog = false">{{ t('Cancel') }}</el-button>
                        <el-button :loading="state.submitRetrieveLoading" @click="onSubmitRetrieve()" type="primary">
                            {{ t('user.login.second') }}
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted, useTemplateRef } from 'vue'
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { sendEms, sendSms } from '/@/api/common'
import { uuid } from '/@/utils/random'
import { useI18n } from 'vue-i18n'
import { buildValidatorData, validatorAccount } from '/@/utils/validate'
import { checkIn, retrievePassword } from '/@/api/frontend/user/index'
import { useEventListener } from '@vueuse/core'
import { onResetForm } from '/@/utils/common'
import { useUserInfo } from '/@/stores/userInfo'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import loginMounted from '/@/components/mixins/loginMounted'
import LoginFooterMixin from '/@/components/mixins/loginFooter.vue'
import type { FormItemRule, FormInstance } from 'element-plus'
import clickCaptcha from '/@/components/clickCaptcha'
let timer: number

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()
const formRef = useTemplateRef('formRef')
const retrieveFormRef = useTemplateRef('retrieveFormRef')

interface State {
    form: {
        tab: 'login' | 'register'
        email: string
        mobile: string
        username: string
        password: string
        captcha: string
        keep: boolean
        captchaId: string
        captchaInfo: string
        registerType: 'email' | 'mobile'
    }
    formLoading: boolean
    showRetrievePasswordDialog: boolean
    retrievePasswordForm: {
        type: 'email' | 'mobile'
        account: string
        captcha: string
        password: string
    }
    dialogWidth: number
    userLoginCaptchaSwitch: boolean
    accountVerificationType: string[]
    codeSendCountdown: number
    submitRetrieveLoading: boolean
    sendCaptchaLoading: boolean
    to: string
}

const state: State = reactive({
    form: {
        tab: 'login',
        email: '',
        mobile: '',
        username: '',
        password: '',
        captcha: '',
        keep: false,
        captchaId: uuid(),
        captchaInfo: '',
        registerType: 'email',
    },
    formLoading: false,
    showRetrievePasswordDialog: false,
    retrievePasswordForm: {
        type: 'email',
        account: '',
        captcha: '',
        password: '',
    },
    dialogWidth: 36,
    userLoginCaptchaSwitch: true,
    accountVerificationType: [],
    codeSendCountdown: 0,
    submitRetrieveLoading: false,
    sendCaptchaLoading: false,
    // 登录成功后要跳转的URL
    to: route.query.to as string,
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    email: [
        buildValidatorData({ name: 'required', title: t('user.login.email') }),
        buildValidatorData({ name: 'email', title: t('user.login.email') }),
    ],
    username: [
        buildValidatorData({ name: 'required', title: t('user.login.User name') }),
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (state.form.tab == 'register') {
                    return validatorAccount(rule, val, callback)
                } else {
                    callback()
                }
            },
            trigger: 'blur',
        },
    ],
    password: [buildValidatorData({ name: 'required', title: t('user.login.password') }), buildValidatorData({ name: 'password' })],
    mobile: [buildValidatorData({ name: 'required', title: t('user.login.mobile') }), buildValidatorData({ name: 'mobile' })],
    captcha: [buildValidatorData({ name: 'required', title: t('user.login.Verification Code') })],
})

const retrieveRules: Partial<Record<string, FormItemRule[]>> = reactive({
    account: [buildValidatorData({ name: 'required', title: t('user.login.Account name') })],
    captcha: [buildValidatorData({ name: 'required', title: t('user.login.Verification Code') })],
    password: [buildValidatorData({ name: 'required', title: t('user.login.password') }), buildValidatorData({ name: 'password' })],
})

const resize = () => {
    let clientWidth = document.documentElement.clientWidth
    let width = 36
    if (clientWidth <= 790) {
        width = 92
    } else if (clientWidth <= 910) {
        width = 56
    } else if (clientWidth <= 1260) {
        width = 46
    }
    state.dialogWidth = width
}

const onSubmitPre = () => {
    formRef.value?.validate((valid) => {
        if (!valid) return
        if (state.form.tab == 'login' && state.userLoginCaptchaSwitch) {
            clickCaptcha(state.form.captchaId, (captchaInfo: string) => onSubmit(captchaInfo))
        } else {
            onSubmit()
        }
    })
}
const onSubmit = (captchaInfo = '') => {
    state.formLoading = true
    state.form.captchaInfo = captchaInfo
    checkIn('post', state.form)
        .then((res) => {
            userInfo.dataFill(res.data.userInfo, false)
            if (state.to) return (location.href = state.to)
            router.push({ path: res.data.routePath })
        })
        .finally(() => {
            state.formLoading = false
        })
}
const onSubmitRetrieve = () => {
    if (!retrieveFormRef.value) return
    retrieveFormRef.value.validate((valid) => {
        if (valid) {
            state.submitRetrieveLoading = true
            retrievePassword(state.retrievePasswordForm)
                .then((res) => {
                    state.submitRetrieveLoading = false
                    if (res.code == 1) {
                        state.showRetrievePasswordDialog = false
                        endTiming()
                        onResetForm(retrieveFormRef.value)
                    }
                })
                .catch(() => {
                    state.submitRetrieveLoading = false
                })
        }
    })
}

const sendRegisterCaptchaPre = () => {
    if (state.codeSendCountdown > 0) return
    formRef.value!.validateField([state.form.registerType, 'username', 'password']).then((valid) => {
        if (!valid) return
        clickCaptcha(state.form.captchaId, (captchaInfo: string) => sendRegisterCaptcha(captchaInfo))
    })
}
const sendRegisterCaptcha = (captchaInfo: string) => {
    state.sendCaptchaLoading = true
    const func = state.form.registerType == 'email' ? sendEms : sendSms
    func(state.form[state.form.registerType], 'user_register', {
        captchaInfo,
        captchaId: state.form.captchaId,
    })
        .then((res) => {
            if (res.code == 1) startTiming(60)
        })
        .finally(() => {
            state.sendCaptchaLoading = false
        })
}

const sendRetrieveCaptchaPre = () => {
    if (state.codeSendCountdown > 0) return
    retrieveFormRef.value!.validateField('account').then((valid) => {
        if (!valid) return
        clickCaptcha(state.form.captchaId, (captchaInfo: string) => sendRetrieveCaptcha(captchaInfo))
    })
}
const sendRetrieveCaptcha = (captchaInfo: string) => {
    state.sendCaptchaLoading = true
    const func = state.retrievePasswordForm.type == 'email' ? sendEms : sendSms
    func(state.retrievePasswordForm.account, 'user_retrieve_pwd', {
        captchaInfo,
        captchaId: state.form.captchaId,
    })
        .then((res) => {
            if (res.code == 1) startTiming(60)
        })
        .finally(() => {
            state.sendCaptchaLoading = false
        })
}

const switchTab = (formRef: FormInstance | null | undefined = undefined, tab: 'login' | 'register') => {
    state.form.tab = tab
    if (tab == 'register') state.form.username = ''
    if (formRef) formRef.clearValidate()
}

const startTiming = (seconds: number) => {
    state.codeSendCountdown = seconds
    timer = window.setInterval(() => {
        state.codeSendCountdown--
        if (state.codeSendCountdown <= 0) {
            endTiming()
        }
    }, 1000)
}
const endTiming = () => {
    state.codeSendCountdown = 0
    clearInterval(timer)
}

onMounted(async () => {
    if (await loginMounted()) return

    resize()
    useEventListener(window, 'resize', resize)

    checkIn('get').then((res) => {
        state.userLoginCaptchaSwitch = res.data.userLoginCaptchaSwitch
        state.accountVerificationType = res.data.accountVerificationType
        state.retrievePasswordForm.type = res.data.accountVerificationType.length > 0 ? res.data.accountVerificationType[0] : ''
    })

    if (route.query.type == 'register') state.form.tab = 'register'
})
onUnmounted(() => {
    state.codeSendCountdown = 0
    endTiming()
})
</script>

<style scoped lang="scss">
.login-box {
    width: 460px;
    margin: 40px auto;
    padding: 10px 60px 20px 60px;
    background-color: var(--ba-bg-color-overlay);
}
.login-title {
    text-align: center;
    font-size: var(--el-font-size-large);
    line-height: 100px;
    user-select: none;
}
:deep(.el-input--large) .el-input__wrapper {
    padding: 4px 15px;
}
.form-buttons {
    padding-top: 20px;
    .el-button {
        width: 100%;
        letter-spacing: 2px;
        font-weight: 300;
        margin-top: 20px;
        margin-left: 0;
    }
}
.register-verification-radio {
    margin-top: 10px;
}
.captcha-box {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    .el-button {
        width: 90%;
        height: 100%;
    }
}
.form-footer {
    display: flex;
    align-items: center;
    .forgot-password {
        color: var(--ba-color-primary-light);
        margin-left: auto;
        user-select: none;
        cursor: pointer;
    }
}
.retrieve-password-form {
    display: flex;
    justify-content: center;
    margin-right: 50px;
}
@media screen and (max-width: 768px) {
    .login-box {
        width: 100%;
        margin: 0 auto;
    }
    .retrieve-password-form {
        margin-right: 0;
    }
}

// 暗黑样式
@at-root .dark {
    .form-buttons {
        .login-btn {
            --el-button-bg-color: var(--el-color-primary-light-5);
            --el-button-border-color: rgba(240, 252, 241, 0.1);
        }
    }
}
</style>
