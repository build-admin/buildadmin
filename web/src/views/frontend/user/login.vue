<template>
    <div class="login">
        <el-container class="is-vertical">
            <Header />
            <el-main class="frontend-footer-brother">
                <el-row justify="center">
                    <el-col :span="16" :xs="24">
                        <div v-if="memberCenter.state.open" class="login-box">
                            <div class="login-title">
                                {{ t('user.user.' + state.form.tab) + t('user.user.reach') + siteConfig.site_name }}
                            </div>
                            <el-form ref="formRef" @keyup.enter="onSubmit(formRef)" :rules="rules" :model="state.form">
                                <!-- 注册验证方式 -->
                                <el-form-item v-if="state.form.tab == 'register'">
                                    <el-radio-group size="large" v-model="state.form.registerType">
                                        <el-radio
                                            class="register-verification-radio"
                                            label="email"
                                            :disabled="!state.accountVerificationType.includes('email')"
                                            border
                                            >{{ t('user.user.Via email') + t('user.user.register') }}</el-radio
                                        >
                                        <el-radio
                                            class="register-verification-radio"
                                            label="mobile"
                                            :disabled="!state.accountVerificationType.includes('mobile')"
                                            border
                                            >{{ t('user.user.Via mobile number') + t('user.user.register') }}</el-radio
                                        >
                                    </el-radio-group>
                                </el-form-item>

                                <!-- 登录注册用户名 -->
                                <el-form-item prop="username">
                                    <el-input
                                        v-model="state.form.username"
                                        :placeholder="
                                            state.form.tab == 'register'
                                                ? t('Please input field', { field: t('user.user.User name') })
                                                : t('Please input field', { field: t('user.user.account') })
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
                                        :placeholder="t('Please input field', { field: t('user.user.password') })"
                                        type="password"
                                        show-password
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <!-- 登录验证码 -->
                                <el-form-item v-if="state.form.tab == 'login'" prop="captcha">
                                    <el-row class="w100">
                                        <el-col :span="16">
                                            <el-input
                                                v-model="state.form.captcha"
                                                clearable
                                                autocomplete="off"
                                                :placeholder="t('Please input field', { field: t('user.user.Verification Code') })"
                                                size="large"
                                            >
                                                <template #prefix>
                                                    <Icon name="fa fa-ellipsis-h" size="16" color="var(--el-input-icon-color)" />
                                                </template>
                                            </el-input>
                                        </el-col>
                                        <el-col class="captcha-box" :span="8">
                                            <img
                                                @click="onChangeCaptcha"
                                                class="captcha-img"
                                                :src="buildCaptchaUrl() + '&id=' + state.form.captchaId"
                                                alt=""
                                            />
                                        </el-col>
                                    </el-row>
                                </el-form-item>

                                <!-- 注册手机号 -->
                                <el-form-item v-if="state.form.tab == 'register' && state.form.registerType == 'mobile'" prop="mobile">
                                    <el-input
                                        v-model="state.form.mobile"
                                        :placeholder="t('Please input field', { field: t('user.user.mobile') })"
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
                                        :placeholder="t('Please input field', { field: t('user.user.mailbox') })"
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
                                                :placeholder="t('Please input field', { field: t('user.user.Verification Code') })"
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
                                                @click="sendRegisterCaptcha(formRef)"
                                                :loading="state.sendCaptchaLoading"
                                                :disabled="state.codeSendCountdown <= 0 ? false : true"
                                                type="primary"
                                                >{{
                                                    state.codeSendCountdown <= 0
                                                        ? t('user.user.send')
                                                        : state.codeSendCountdown + t('user.user.seconds')
                                                }}</el-button
                                            >
                                        </el-col>
                                    </el-row>
                                </el-form-item>

                                <div v-if="state.form.tab != 'register'" class="form-footer">
                                    <el-checkbox v-model="state.form.keep" :label="t('user.user.Remember me')" size="default"></el-checkbox>
                                    <div
                                        v-if="state.accountVerificationType.length > 0"
                                        @click="state.showRetrievePasswordDialog = true"
                                        class="forgot-password"
                                    >
                                        {{ t('user.user.Forgot your password?') }}
                                    </div>
                                </div>
                                <el-form-item class="form-buttons">
                                    <el-button @click="onSubmit(formRef)" :loading="state.formLoading" round type="primary" size="large">
                                        {{ t('user.user.' + state.form.tab) }}
                                    </el-button>
                                    <el-button
                                        v-if="state.form.tab == 'register'"
                                        @click="state.form.tab = 'login'"
                                        round
                                        plain
                                        type="info"
                                        size="large"
                                        >{{ t('user.user.Back to login') }}</el-button
                                    >
                                    <el-button v-else @click="state.form.tab = 'register'" round plain type="info" size="large">
                                        {{ t('user.user.No account yet? Click Register') }}
                                    </el-button>
                                </el-form-item>
                            </el-form>
                        </div>
                        <el-alert v-else :center="true" :title="$t('user.user.Member center disabled')" type="error" />
                    </el-col>
                </el-row>
            </el-main>
            <Footer />
        </el-container>

        <el-dialog
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            v-model="state.showRetrievePasswordDialog"
            :title="t('user.user.Retrieve password')"
            :width="state.dialogWidth + '%'"
            :draggable="true"
        >
            <div class="retrieve-password-form">
                <el-form
                    ref="retrieveFormRef"
                    @keyup.enter="onSubmitRetrieve(retrieveFormRef)"
                    :rules="retrieveRules"
                    :model="state.retrievePasswordForm"
                    :label-width="100"
                >
                    <el-form-item :label="t('user.user.Retrieval method')">
                        <el-radio-group v-model="state.retrievePasswordForm.type">
                            <el-radio label="email" :disabled="!state.accountVerificationType.includes('email')" border>{{
                                t('user.user.Via email')
                            }}</el-radio>
                            <el-radio label="mobile" :disabled="!state.accountVerificationType.includes('mobile')" border>{{
                                t('user.user.Via mobile number')
                            }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item prop="account" :label="state.retrievePasswordForm.type == 'email' ? t('user.user.mailbox') : t('user.user.mobile')">
                        <el-input
                            v-model="state.retrievePasswordForm.account"
                            :placeholder="
                                t('Please input field', {
                                    field: state.retrievePasswordForm.type == 'email' ? t('user.user.mailbox') : t('user.user.mobile'),
                                })
                            "
                            :clearable="true"
                        >
                            <template #prefix>
                                <Icon name="fa fa-user" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="captcha" :label="t('user.user.Verification Code')">
                        <el-row class="w100">
                            <el-col :span="16">
                                <el-input
                                    v-model="state.retrievePasswordForm.captcha"
                                    :placeholder="t('Please input field', { field: t('user.user.Verification Code') })"
                                    autocomplete="off"
                                >
                                    <template #prefix>
                                        <Icon name="fa fa-ellipsis-h" size="16" color="var(--el-input-icon-color)" />
                                    </template>
                                </el-input>
                            </el-col>
                            <el-col class="captcha-box" :span="8">
                                <el-button
                                    @click="sendRetrieveCaptcha(retrieveFormRef)"
                                    :loading="state.sendCaptchaLoading"
                                    :disabled="state.codeSendCountdown <= 0 ? false : true"
                                    type="primary"
                                    >{{
                                        state.codeSendCountdown <= 0 ? t('user.user.send') : state.codeSendCountdown + t('user.user.seconds')
                                    }}</el-button
                                >
                            </el-col>
                        </el-row>
                    </el-form-item>
                    <el-form-item prop="password" :label="t('user.user.New password')">
                        <el-input
                            v-model="state.retrievePasswordForm.password"
                            :placeholder="t('Please input field', { field: t('user.user.New password') })"
                            show-password
                        >
                            <template #prefix>
                                <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="state.showRetrievePasswordDialog = false">{{ t('Cancel') }}</el-button>
                        <el-button :loading="state.submitRetrieveLoading" @click="onSubmitRetrieve(retrieveFormRef)" type="primary">{{
                            t('user.user.second')
                        }}</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted, ref } from 'vue'
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { buildCaptchaUrl } from '/@/api/common'
import { uuid } from '/@/utils/random'
import { useI18n } from 'vue-i18n'
import { buildValidatorData, validatorAccount } from '/@/utils/validate'
import { checkIn, sendRetrievePasswordCode, retrievePassword, sendRegisterCode } from '/@/api/frontend/user/index'
import { onResetForm } from '/@/utils/common'
import { useUserInfo } from '/@/stores/userInfo'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import type { ElForm, FormItemRule } from 'element-plus'
var timer: NodeJS.Timer

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()
const formRef = ref<InstanceType<typeof ElForm>>()
const retrieveFormRef = ref<InstanceType<typeof ElForm>>()

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
        registerType: 'email' | 'mobile'
    }
    formLoading: boolean
    showCaptcha: boolean
    showRetrievePasswordDialog: boolean
    retrievePasswordForm: {
        type: 'email' | 'mobile'
        account: string
        captcha: string
        password: string
    }
    dialogWidth: number
    accountVerificationType: string[]
    codeSendCountdown: number
    submitRetrieveLoading: boolean
    sendCaptchaLoading: boolean
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
        registerType: 'email',
    },
    formLoading: false,
    showCaptcha: false,
    showRetrievePasswordDialog: false,
    retrievePasswordForm: {
        type: 'email',
        account: '',
        captcha: '',
        password: '',
    },
    dialogWidth: 36,
    accountVerificationType: [],
    codeSendCountdown: 0,
    submitRetrieveLoading: false,
    sendCaptchaLoading: false,
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    email: [buildValidatorData('required', t('user.user.mailbox')), buildValidatorData('email', t('user.user.mailbox'))],
    username: [
        buildValidatorData('required', t('user.user.User name')),
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
    password: [buildValidatorData('required', t('user.user.password')), buildValidatorData('password')],
    mobile: [buildValidatorData('required', t('user.user.mobile')), buildValidatorData('mobile')],
    captcha: [buildValidatorData('required', t('user.user.Verification Code'))],
})

const retrieveRules: Partial<Record<string, FormItemRule[]>> = reactive({
    account: [buildValidatorData('required', t('user.user.Account name'))],
    captcha: [buildValidatorData('required', t('user.user.Verification Code'))],
    password: [buildValidatorData('required', t('user.user.password')), buildValidatorData('password')],
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

const onChangeCaptcha = () => {
    state.form.captcha = ''
    state.form.captchaId = uuid()
}
const onSubmit = (formRef: InstanceType<typeof ElForm> | undefined = undefined) => {
    formRef!.validate((valid) => {
        if (valid) {
            state.formLoading = true
            checkIn('post', state.form)
                .then((res) => {
                    state.formLoading = false
                    userInfo.$state = res.data.userinfo
                    router.push({ path: res.data.routePath })
                })
                .catch(() => {
                    state.formLoading = false
                    onChangeCaptcha()
                })
        } else {
            onChangeCaptcha()
        }
    })
}
const onSubmitRetrieve = (formRef: InstanceType<typeof ElForm> | undefined = undefined) => {
    formRef!.validate((valid) => {
        if (valid) {
            state.submitRetrieveLoading = true
            retrievePassword(state.retrievePasswordForm)
                .then((res) => {
                    state.submitRetrieveLoading = false
                    if (res.code == 1) {
                        state.showRetrievePasswordDialog = false
                        onChangeCaptcha()
                        endTiming()
                        onResetForm(formRef)
                    }
                })
                .catch(() => {
                    state.submitRetrieveLoading = false
                })
        }
    })
}

const sendRegisterCaptcha = (formRef: InstanceType<typeof ElForm> | undefined = undefined) => {
    if (state.codeSendCountdown > 0) return
    formRef!.validateField([state.form.registerType, 'username', 'password']).then((valid) => {
        if (valid) {
            state.sendCaptchaLoading = true
            sendRegisterCode(state.form)
                .then((res) => {
                    state.sendCaptchaLoading = false
                    if (res.code == 1) {
                        startTiming(60)
                    }
                })
                .catch(() => {
                    state.sendCaptchaLoading = false
                })
        }
    })
}

const sendRetrieveCaptcha = (formRef: InstanceType<typeof ElForm> | undefined = undefined) => {
    if (state.codeSendCountdown > 0) return
    formRef!.validateField('account').then((valid) => {
        if (valid) {
            state.sendCaptchaLoading = true
            sendRetrievePasswordCode(state.retrievePasswordForm.type, state.retrievePasswordForm.account)
                .then((res) => {
                    state.sendCaptchaLoading = false
                    if (res.code == 1) {
                        startTiming(60)
                    }
                })
                .catch(() => {
                    state.sendCaptchaLoading = false
                })
        }
    })
}

const startTiming = (seconds: number) => {
    state.codeSendCountdown = seconds
    timer = setInterval(() => {
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

onMounted(() => {
    resize()
    window.addEventListener('resize', resize)

    checkIn('get').then((res) => {
        state.accountVerificationType = res.data.accountVerificationType
        state.retrievePasswordForm.type = res.data.accountVerificationType.length > 0 ? res.data.accountVerificationType[0] : ''
    })

    if (route.query.type == 'register') state.form.tab = 'register'
})
onUnmounted(() => {
    window.removeEventListener('resize', resize)
    state.codeSendCountdown = 0
    endTiming()
})
</script>

<style scoped lang="scss">
.login-box {
    width: 460px;
    margin: 40px auto;
    padding: 10px 60px 70px 60px;
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
    .captcha-img {
        width: 90%;
        margin-left: auto;
    }
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
</style>
