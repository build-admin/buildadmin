<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>{{ $t('user.account.profile.profile') }}</span>
                    <el-button @click="router.push({ name: 'account/changePassword' })" type="info" v-blur plain>
                        {{ $t('user.account.profile.Change Password') }}
                    </el-button>
                </div>
            </template>
            <div class="user-profile">
                <el-form
                    :label-position="memberCenter.state.shrink ? 'top' : 'right'"
                    :model="state.form"
                    :rules="state.rules"
                    :label-width="100"
                    ref="formRef"
                    @keyup.enter="onSubmit()"
                >
                    <FormItem
                        :label="$t('user.account.profile.avatar')"
                        :input-attr="{
                            hideSelectFile: true,
                        }"
                        type="image"
                        v-model="state.form.avatar"
                        prop="avatar"
                    />
                    <FormItem
                        :label="$t('user.account.profile.User name')"
                        type="string"
                        v-model="state.form.username"
                        :placeholder="$t('Please input field', { field: $t('user.account.profile.User name') })"
                        prop="username"
                    />
                    <FormItem
                        :label="$t('user.account.profile.User nickname')"
                        type="string"
                        v-model="state.form.nickname"
                        :placeholder="$t('Please input field', { field: $t('user.account.profile.User nickname') })"
                        prop="nickname"
                    />
                    <el-form-item v-if="state.accountVerificationType.includes('email')" :label="t('user.account.profile.email')">
                        <el-input v-model="state.form.email" readonly :placeholder="t('user.account.profile.Operation via right button')">
                            <template #append>
                                <el-button type="primary" @click="onChangeBindInfo('email')">
                                    {{ state.form.email ? t('user.account.profile.Click Modify') : t('user.account.profile.bind') }}
                                </el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item v-if="state.accountVerificationType.includes('mobile')" :label="t('user.account.profile.mobile')">
                        <el-input v-model="state.form.mobile" readonly :placeholder="t('user.account.profile.Operation via right button')">
                            <template #append>
                                <el-button type="primary" @click="onChangeBindInfo('mobile')">
                                    {{ state.form.mobile ? t('user.account.profile.Click Modify') : t('user.account.profile.bind') }}
                                </el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <FormItem
                        :label="$t('user.account.profile.Gender')"
                        type="radio"
                        v-model="state.form.gender"
                        :input-attr="{
                            border: true,
                            content: {
                                '0': $t('user.account.profile.secrecy'),
                                '1': $t('user.account.profile.male'),
                                '2': $t('user.account.profile.female'),
                            },
                        }"
                    />
                    <FormItem :label="$t('user.account.profile.birthday')" type="date" v-model="state.form.birthday" />
                    <FormItem
                        :label="$t('user.account.profile.Personal signature')"
                        type="textarea"
                        :placeholder="$t('Please input field', { field: $t('user.account.profile.Personal signature') })"
                        v-model="state.form.motto"
                        :input-attr="{ showWordLimit: true, maxlength: 120, rows: 3 }"
                    />
                    <UserProfileMixin />
                    <el-form-item class="submit-buttons">
                        <el-button @click="onResetForm(formRef)">{{ $t('Reset') }}</el-button>
                        <el-button type="primary" :loading="state.formSubmitLoading" @click="onSubmit()">{{ $t('Save') }}</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>

        <!-- 账户验证 -->
        <el-dialog
            :title="t('user.account.profile.Account verification')"
            v-model="state.dialog.verification.show"
            class="ba-change-bind-dialog ba-verification-dialog"
            :destroy-on-close="true"
            :close-on-click-modal="false"
            width="30%"
        >
            <el-form
                :model="state.dialog.verification.form"
                :rules="state.dialog.verification.rules"
                :label-position="'top'"
                ref="verificationFormRef"
                @keyup.enter="onSubmitVerification()"
            >
                <FormItem
                    :label="t('user.account.profile.Account password verification')"
                    type="password"
                    v-model="state.dialog.verification.form.password"
                    prop="password"
                    :input-attr="{ showPassword: true }"
                    :placeholder="$t('Please input field', { field: $t('user.account.profile.password') })"
                />
                <el-form-item prop="captcha">
                    <template #label>
                        <span v-if="state.dialog.type == 'email'">
                            {{ t('user.account.profile.Mail verification') }}
                            ({{ t('user.account.profile.accept') + t('user.account.profile.mail') + '：' + userInfo.email }})
                        </span>
                        <span v-else>
                            {{ t('user.account.profile.SMS verification') }}
                            ({{ t('user.account.profile.accept') + t('user.account.profile.mobile') + '：' + userInfo.mobile }})
                        </span>
                    </template>
                    <el-row class="w100" :gutter="10">
                        <el-col :span="18">
                            <el-input
                                v-model="state.dialog.verification.form.captcha"
                                :placeholder="t('Please input field', { field: t('user.account.profile.Verification Code') })"
                                autocomplete="off"
                            />
                        </el-col>
                        <el-col class="captcha-box" :span="6">
                            <el-button
                                @click="sendVerificationCaptchaPre"
                                :loading="state.dialog.sendCaptchaLoading"
                                :disabled="state.dialog.codeSendCountdown <= 0 ? false : true"
                                type="primary"
                            >
                                {{
                                    state.dialog.codeSendCountdown <= 0
                                        ? t('user.account.profile.send')
                                        : state.dialog.codeSendCountdown + t('user.account.profile.seconds')
                                }}
                            </el-button>
                        </el-col>
                    </el-row>
                </el-form-item>
            </el-form>
            <template #footer>
                <div :style="'width: calc(100% - 20px)'">
                    <el-button @click="state.dialog.verification.show = false">{{ t('Cancel') }}</el-button>
                    <el-button v-blur :loading="state.dialog.submitLoading" @click="onSubmitVerification()" type="primary">
                        {{ t('user.account.profile.next step') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>

        <!-- 绑定 -->
        <el-dialog
            :title="t('user.account.profile.bind') + t('user.account.profile.' + state.dialog.type)"
            v-model="state.dialog.bind.show"
            class="ba-change-bind-dialog ba-bind-dialog"
            :destroy-on-close="true"
            :close-on-click-modal="false"
            width="30%"
        >
            <el-form
                :model="state.dialog.bind.form"
                :rules="state.dialog.bind.rules"
                :label-position="'top'"
                ref="bindFormRef"
                @keyup.enter="onSubmitBind()"
            >
                <FormItem
                    v-if="!state.dialog.verification.accountVerificationToken"
                    :label="t('user.account.profile.Account password verification')"
                    type="password"
                    v-model="state.dialog.bind.form.password"
                    prop="password"
                    :input-attr="{ showPassword: true }"
                    :placeholder="$t('Please input field', { field: $t('user.account.profile.password') })"
                />
                <FormItem
                    v-if="state.dialog.type == 'email'"
                    :label="t('user.account.profile.New ' + state.dialog.type)"
                    type="string"
                    v-model="state.dialog.bind.form.email"
                    prop="email"
                    :placeholder="$t('Please input field', { field: t('user.account.profile.New ' + state.dialog.type) })"
                />
                <FormItem
                    v-if="state.dialog.type == 'mobile'"
                    :label="t('user.account.profile.New ' + state.dialog.type)"
                    type="string"
                    v-model="state.dialog.bind.form.mobile"
                    prop="mobile"
                    :placeholder="$t('Please input field', { field: t('user.account.profile.New ' + state.dialog.type) })"
                />
                <el-form-item
                    :label="state.dialog.type == 'email' ? t('user.account.profile.Mail verification') : t('user.account.profile.SMS verification')"
                    prop="captcha"
                >
                    <el-row class="w100" :gutter="10">
                        <el-col :span="18">
                            <el-input
                                v-model="state.dialog.bind.form.captcha"
                                :placeholder="t('Please input field', { field: t('user.account.profile.Verification Code') })"
                                autocomplete="off"
                            />
                        </el-col>
                        <el-col class="captcha-box" :span="6">
                            <el-button
                                @click="sendBindCaptchaPre"
                                :loading="state.dialog.sendCaptchaLoading"
                                :disabled="state.dialog.codeSendCountdown <= 0 ? false : true"
                                type="primary"
                            >
                                {{
                                    state.dialog.codeSendCountdown <= 0
                                        ? t('user.account.profile.send')
                                        : state.dialog.codeSendCountdown + t('user.account.profile.seconds')
                                }}
                            </el-button>
                        </el-col>
                    </el-row>
                </el-form-item>
            </el-form>
            <template #footer>
                <div :style="'width: calc(100% - 20px)'">
                    <el-button @click="state.dialog.bind.show = false">{{ t('Cancel') }}</el-button>
                    <el-button v-blur :loading="state.dialog.submitLoading" @click="onSubmitBind()" type="primary">
                        {{ t('user.account.profile.bind') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, useTemplateRef } from 'vue'
import { useRouter } from 'vue-router'
import type { FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { useUserInfo } from '/@/stores/userInfo'
import { onResetForm } from '/@/utils/common'
import { buildValidatorData } from '/@/utils/validate'
import { getProfile, postProfile, postVerification, postChangeBind } from '/@/api/frontend/user/index'
import UserProfileMixin from '/@/components/mixins/userProfile.vue'
import { useI18n } from 'vue-i18n'
import { sendEms, sendSms } from '/@/api/common'
import { uuid } from '/@/utils/random'
import clickCaptcha from '/@/components/clickCaptcha'
import { useMemberCenter } from '/@/stores/memberCenter'
let timer: number

const { t } = useI18n()
const router = useRouter()
const userInfo = useUserInfo()
const memberCenter = useMemberCenter()

const formRef = useTemplateRef('formRef')
const bindFormRef = useTemplateRef('bindFormRef')
const verificationFormRef = useTemplateRef('verificationFormRef')

const state: {
    formSubmitLoading: boolean
    form: anyObj
    rules: Partial<Record<string, FormItemRule[]>>
    accountVerificationType: string[]
    dialog: {
        type: 'email' | 'mobile'
        submitLoading: boolean
        sendCaptchaLoading: boolean
        codeSendCountdown: number
        captchaId: string
        verification: {
            show: boolean
            rules: Partial<Record<string, FormItemRule[]>>
            form: {
                password: string
                captcha: string
            }
            accountVerificationToken: string
        }
        bind: {
            show: boolean
            rules: Partial<Record<string, FormItemRule[]>>
            form: {
                password: string
                email: string
                mobile: string
                captcha: string
            }
        }
    }
} = reactive({
    formSubmitLoading: false,
    form: userInfo.$state,
    rules: {
        username: [buildValidatorData({ name: 'required', title: t('user.account.profile.User name') }), buildValidatorData({ name: 'account' })],
        nickname: [buildValidatorData({ name: 'required', title: t('user.account.profile.nickname') })],
    },
    accountVerificationType: [],
    dialog: {
        type: 'email',
        submitLoading: false,
        sendCaptchaLoading: false,
        codeSendCountdown: 0,
        captchaId: uuid(),
        verification: {
            show: false,
            rules: {
                password: [
                    buildValidatorData({ name: 'required', title: t('user.account.profile.password') }),
                    buildValidatorData({ name: 'password' }),
                ],
                captcha: [buildValidatorData({ name: 'required', title: t('user.account.profile.Verification Code') })],
            },
            form: {
                password: '',
                captcha: '',
            },
            accountVerificationToken: '',
        },
        bind: {
            show: false,
            rules: {
                password: [
                    buildValidatorData({ name: 'required', title: t('user.account.profile.password') }),
                    buildValidatorData({ name: 'password' }),
                ],
                email: [
                    buildValidatorData({ name: 'required', title: t('user.account.profile.email') }),
                    buildValidatorData({ name: 'email', title: t('user.account.profile.email') }),
                ],
                mobile: [
                    buildValidatorData({ name: 'required', title: t('user.account.profile.mobile') }),
                    buildValidatorData({ name: 'mobile', title: t('user.account.profile.mobile') }),
                ],
                captcha: [buildValidatorData({ name: 'required', title: t('user.account.profile.Verification Code') })],
            },
            form: {
                password: '',
                email: '',
                mobile: '',
                captcha: '',
            },
        },
    },
})

const startTiming = (seconds: number) => {
    state.dialog.codeSendCountdown = seconds
    timer = window.setInterval(() => {
        state.dialog.codeSendCountdown--
        if (state.dialog.codeSendCountdown <= 0) {
            endTiming()
        }
    }, 1000)
}
const endTiming = () => {
    state.dialog.codeSendCountdown = 0
    clearInterval(timer)
}

const onChangeBindInfo = (type: 'email' | 'mobile') => {
    if ((type == 'email' && userInfo.email) || (type == 'mobile' && userInfo.mobile)) {
        state.dialog.verification.show = true
    } else {
        state.dialog.bind.show = true
    }
    state.dialog.type = type
}

const sendVerificationCaptchaPre = () => {
    if (state.dialog.codeSendCountdown > 0) return
    verificationFormRef.value!.validateField('password').then((res) => {
        if (!res) return
        clickCaptcha(state.dialog.captchaId, (captchaInfo: string) => sendVerificationCaptcha(captchaInfo))
    })
}
const sendVerificationCaptcha = (captchaInfo: string) => {
    state.dialog.sendCaptchaLoading = true
    const func = state.dialog.type == 'email' ? sendEms : sendSms
    func(userInfo[state.dialog.type], `user_${state.dialog.type}_verify`, {
        password: state.dialog.verification.form.password,
        captchaId: state.dialog.captchaId,
        captchaInfo,
    })
        .then((res) => {
            if (res.code == 1) startTiming(60)
        })
        .finally(() => {
            state.dialog.sendCaptchaLoading = false
        })
}

const sendBindCaptchaPre = () => {
    if (state.dialog.codeSendCountdown > 0) return
    bindFormRef.value!.validateField(state.dialog.type).then((res) => {
        if (!res) return
        clickCaptcha(state.dialog.captchaId, (captchaInfo: string) => sendBindCaptcha(captchaInfo))
    })
}
const sendBindCaptcha = (captchaInfo: string) => {
    state.dialog.sendCaptchaLoading = true
    const func = state.dialog.type == 'email' ? sendEms : sendSms
    func(state.dialog.bind.form[state.dialog.type], `user_change_${state.dialog.type}`, {
        captchaId: state.dialog.captchaId,
        captchaInfo,
    })
        .then((res) => {
            if (res.code == 1) startTiming(60)
        })
        .finally(() => {
            state.dialog.sendCaptchaLoading = false
        })
}

const onSubmitVerification = () => {
    verificationFormRef.value?.validate((res) => {
        if (res) {
            state.dialog.submitLoading = true
            postVerification({
                type: state.dialog.type,
                captcha: state.dialog.verification.form.captcha,
            })
                .then((res) => {
                    endTiming()
                    state.dialog.bind.show = true
                    state.dialog.type = res.data.type
                    state.dialog.verification.show = false
                    state.dialog.verification.accountVerificationToken = res.data.accountVerificationToken
                })
                .finally(() => {
                    state.dialog.submitLoading = false
                })
        }
    })
}

const onSubmitBind = () => {
    bindFormRef.value?.validate((res) => {
        if (res) {
            state.dialog.submitLoading = true
            postChangeBind({
                type: state.dialog.type,
                accountVerificationToken: state.dialog.verification.accountVerificationToken,
                ...state.dialog.bind.form,
            })
                .then(() => {
                    endTiming()
                    state.dialog.bind.show = false
                    userInfo[state.dialog.type] = state.dialog.bind.form[state.dialog.type]
                })
                .finally(() => {
                    state.dialog.submitLoading = false
                })
        }
    })
}

const onSubmit = () => {
    formRef.value?.validate((valid) => {
        if (valid) {
            state.formSubmitLoading = true
            postProfile(state.form)
                .then(() => {
                    state.formSubmitLoading = false
                })
                .catch(() => {
                    state.formSubmitLoading = false
                })
        }
    })
}

onMounted(() => {
    getProfile().then((res) => {
        state.accountVerificationType = res.data.accountVerificationType
    })
})
</script>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.user-profile {
    width: 400px;
    max-width: 100%;
}
.submit-buttons :deep(.el-form-item__content) {
    justify-content: flex-end;
}
:deep(.el-upload-list--picture-card) {
    --el-upload-list-picture-card-size: 100px;
}
:deep(.el-upload--picture-card) {
    --el-upload-picture-card-size: 100px;
}
.captcha-box {
    margin-left: auto;
    .el-button {
        width: 100%;
    }
}
:deep(.ba-verification-dialog) .el-dialog__body {
    padding-bottom: 10px;
}
@media screen and (max-width: 1024px) {
    :deep(.ba-change-bind-dialog) {
        --el-dialog-width: 50% !important;
    }
}
@media screen and (max-width: 768px) {
    :deep(.ba-change-bind-dialog) {
        --el-dialog-width: 70% !important;
    }
}
@media screen and (max-width: 600px) {
    :deep(.ba-change-bind-dialog) {
        --el-dialog-width: 92% !important;
    }
}
</style>
