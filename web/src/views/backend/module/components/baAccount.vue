<template>
    <div>
        <el-dialog v-model="state.dialog.baAccount" class="ba-account-dialog" width="25%" :title="t('module.Member information')">
            <template v-if="baAccount.token">
                <div v-loading="state.loading.common" class="userinfo">
                    <div class="user-avatar-box">
                        <img class="user-avatar" :src="baAccount.avatar" alt="" />
                        <Icon
                            class="user-avatar-gender"
                            :name="baAccount.getGenderIcon()['name']"
                            size="14"
                            :color="baAccount.getGenderIcon()['color']"
                        />
                    </div>
                    <p class="username">{{ baAccount.nickname }}</p>
                    <p class="user-integral">
                        <span>{{ $t('user.user.integral') + ' ' + baAccount.score }}</span>
                        <span>{{ $t('user.user.balance') + ' ' + baAccount.money }}</span>
                    </p>
                    <div class="userinfo-buttons">
                        <!-- <el-button v-blur size="default" type="primary">{{ $('module.My module') }}</el-button> -->
                        <el-button @click="baAccount.logout()" v-blur size="default" type="warning">{{ $t('user.user.Logout login') }}</el-button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="ba-login">
                    <h3 class="ba-title">{{ t('module.Log in to the buildadmin module marketplace') }}</h3>
                    <el-form
                        @keyup.enter="onBaAccountSubmit(baAccountFormRef)"
                        ref="baAccountFormRef"
                        :rules="baAccountFormRules"
                        class="ba-account-login-form"
                        :model="user.form"
                    >
                        <FormItem
                            v-model="user.form.username"
                            type="string"
                            prop="username"
                            :placeholder="t('module.Please enter buildadmin account name or email')"
                            :input-attr="{
                                size: 'large',
                            }"
                        />
                        <FormItem
                            v-model="user.form.password"
                            type="password"
                            prop="password"
                            :placeholder="t('module.Please enter the buildadmin account password')"
                            :input-attr="{
                                size: 'large',
                            }"
                        />
                        <!-- 登录注册验证码 -->
                        <el-form-item prop="captcha">
                            <el-row class="w100">
                                <el-col :span="16">
                                    <el-input
                                        v-model="user.form.captcha"
                                        size="large"
                                        clearable
                                        autocomplete="off"
                                        :placeholder="t('module.Please enter the login verification code')"
                                    >
                                    </el-input>
                                </el-col>
                                <el-col class="captcha-box" :span="8">
                                    <img @click="onChangeCaptcha" class="captcha-img" :src="buildCaptchaUrl() + '&id=' + user.form.captchaId" />
                                </el-col>
                            </el-row>
                        </el-form-item>
                        <el-form-item class="form-buttons">
                            <el-button @click="onBaAccountSubmit(baAccountFormRef)" :loading="user.loading" round type="primary" size="large">
                                {{ t('module.Sign in') }}
                            </el-button>
                            <a target="_blank" class="ba-account-register" href="https://ba.buildadmin.com/#/user/login?type=register">
                                <el-button round plain type="info" size="large"> {{ t('module.Register') }} </el-button>
                            </a>
                        </el-form-item>
                    </el-form>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { useI18n } from 'vue-i18n'
import { uuid } from '/@/utils/random'
import { checkIn, buildCaptchaUrl } from '/@/api/backend/module'
import { useBaAccount } from '/@/stores/baAccount'
import { state } from '../store'

const { t } = useI18n()
const baAccount = useBaAccount()
const baAccountFormRef = ref<FormInstance>()
const user: {
    loading: boolean
    form: {
        tab: 'login' | 'register'
        username: string
        password: string
        captcha: string
        captchaId: string
        keep: boolean
    }
} = reactive({
    loading: false,
    form: {
        tab: 'login',
        username: '',
        password: '',
        captcha: '',
        captchaId: uuid(),
        keep: false,
    },
})

const baAccountFormRules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [buildValidatorData({ name: 'required', title: t('user.user.User name') })],
    captcha: [buildValidatorData({ name: 'required', title: t('user.user.Verification Code') })],
    password: [buildValidatorData({ name: 'required', title: t('user.user.password') }), buildValidatorData({ name: 'password' })],
})

const onBaAccountSubmit = (formRef: FormInstance | undefined = undefined) => {
    formRef!.validate((valid) => {
        if (valid) {
            user.loading = true
            checkIn('post', user.form)
                .then((res) => {
                    state.dialog.baAccount = false
                    user.loading = false
                    baAccount.dataFill(res.data.userinfo)
                })
                .catch(() => {
                    user.loading = false
                    onChangeCaptcha()
                })
        } else {
            onChangeCaptcha()
        }
    })
}

const onChangeCaptcha = () => {
    user.form.captcha = ''
    user.form.captchaId = uuid()
}
</script>

<style scoped lang="scss">
.userinfo {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
    .username {
        display: block;
        text-align: center;
        width: 100%;
        padding-top: 10px;
        font-size: var(--el-font-size-large);
        font-weight: bold;
    }
    .user-integral {
        display: block;
        text-align: center;
        width: 100%;
        padding: 10px 0;
        font-size: var(--el-font-size-base);
        span {
            padding: 0 4px;
        }
    }
    .user-avatar-box {
        position: relative;
        cursor: pointer;
    }
    .user-avatar {
        display: block;
        width: 100px;
        border-radius: 50%;
        border: 1px solid var(--el-border-color-extra-light);
    }
    .user-avatar-gender {
        position: absolute;
        bottom: 0px;
        right: 10px;
        height: 22px;
        width: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        border-radius: 50%;
        box-shadow: var(--el-box-shadow);
    }
    .userinfo-buttons {
        margin-top: 10px;
    }
}

.ba-login {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;

    .ba-title {
        width: 100%;
        text-align: center;
    }

    .captcha-box {
        display: flex;
        align-items: center;
        justify-content: center;

        .captcha-img {
            width: 90%;
            margin-left: auto;
        }

        .el-button {
            width: 90%;
        }
    }

    .form-buttons {
        .el-button {
            width: 100%;
            letter-spacing: 2px;
            font-weight: 300;
            margin-top: 20px;
            margin-left: 0;
        }
    }
    .ba-account-register {
        width: 100%;
        text-decoration: none;
    }
    .ba-account-login-form {
        width: 350px;
        padding-top: 20px;
    }
}

/* 会员信息弹窗-s */
@media screen and (max-width: 1440px) {
    :deep(.ba-account-dialog) {
        --el-dialog-width: 40% !important;
    }
}
@media screen and (max-width: 1024px) {
    :deep(.ba-account-dialog) {
        --el-dialog-width: 70% !important;
    }
}
/* 会员信息弹窗-e */
</style>
