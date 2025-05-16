<!-- 模块市场 和 CRUD 记录页面等地方的 BuildAdmin 官方账户登录弹窗 -->
<template>
    <div>
        <el-dialog v-model="model" class="ba-account-dialog" width="25%" :title="t('layouts.Member information')">
            <template v-if="baAccount.token">
                <div v-loading="state.loading" class="userinfo">
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
                    <p class="user-info">
                        <span>{{ $t('Integral') + ' ' + baAccount.score }}</span>
                        <span>{{ $t('Balance') + ' ' + baAccount.money }}</span>
                    </p>
                    <div class="userinfo-buttons">
                        <a href="https://uni.buildadmin.com/user" target="_blank" rel="noopener noreferrer">
                            <el-button v-blur size="default" type="primary">
                                {{ $t('layouts.Member center') }}
                            </el-button>
                        </a>
                        <el-button @click="baAccount.logout()" v-blur size="default" type="warning">{{ $t('layouts.Logout') }}</el-button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="ba-login">
                    <h3 class="ba-title">{{ t('layouts.Login to the buildadmin') }}</h3>
                    <el-form
                        @keyup.enter="onBaAccountSubmitPre()"
                        ref="baAccountFormRef"
                        :rules="baAccountFormRules"
                        class="ba-account-login-form"
                        :model="state.user"
                    >
                        <FormItem
                            v-model="state.user.username"
                            type="string"
                            prop="username"
                            :placeholder="t('layouts.Please enter buildadmin account name or email')"
                            :input-attr="{
                                size: 'large',
                            }"
                        />
                        <FormItem
                            v-model="state.user.password"
                            type="password"
                            prop="password"
                            :placeholder="t('layouts.Please enter the buildadmin account password')"
                            :input-attr="{
                                size: 'large',
                            }"
                        />
                        <el-form-item class="form-buttons">
                            <el-button @click="onBaAccountSubmitPre()" :loading="state.submitLoading" round type="primary" size="large">
                                {{ t('layouts.Login') }}
                            </el-button>
                            <a
                                target="_blank"
                                class="ba-account-register"
                                href="https://uni.buildadmin.com/user/login?type=register"
                                rel="noopener noreferrer"
                            >
                                <el-button round plain type="info" size="large"> {{ t('layouts.Register') }} </el-button>
                            </a>
                        </el-form-item>
                    </el-form>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import type { FormItemRule } from 'element-plus'
import { reactive, useTemplateRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { baAccountCheckIn, baAccountGetUserInfo } from '/@/api/backend/index'
import clickCaptcha from '/@/components/clickCaptcha'
import FormItem from '/@/components/formItem/index.vue'
import { useBaAccount } from '/@/stores/baAccount'
import { useSiteConfig } from '/@/stores/siteConfig'
import { uuid } from '/@/utils/random'
import { buildValidatorData } from '/@/utils/validate'

const { t } = useI18n()
const baAccount = useBaAccount()
const siteConfig = useSiteConfig()
const model = defineModel<boolean>()
const baAccountFormRef = useTemplateRef('baAccountFormRef')

interface Props {
    loginCallback?: (res: ApiResponse) => void
}

const props = withDefaults(defineProps<Props>(), {
    loginCallback: () => {},
})

const state = reactive({
    loading: true,
    submitLoading: false,
    user: {
        tab: 'login',
        username: '',
        password: '',
        captchaId: uuid(),
        captchaInfo: '',
        keep: false,
    },
})

const onBaAccountSubmitPre = () => {
    baAccountFormRef.value?.validate((valid) => {
        if (valid) {
            clickCaptcha(state.user.captchaId, (captchaInfo: string) => onBaAccountSubmit(captchaInfo), { apiBaseURL: siteConfig.apiUrl })
        }
    })
}

const onBaAccountSubmit = (captchaInfo = '') => {
    state.submitLoading = true
    state.user.captchaInfo = captchaInfo
    baAccountCheckIn(state.user)
        .then((res) => {
            baAccount.dataFill(res.data.userInfo, false)
            props.loginCallback(res)
        })
        .finally(() => {
            state.submitLoading = false
        })
}

const baAccountFormRules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [buildValidatorData({ name: 'required', title: t('layouts.Username') })],
    password: [buildValidatorData({ name: 'required', title: t('layouts.Password') }), buildValidatorData({ name: 'password' })],
})

watch(
    () => model.value,
    (newVal) => {
        if (newVal && baAccount.token) {
            baAccountGetUserInfo()
                .then((res) => {
                    baAccount.dataFill(res.data.userInfo)
                })
                .catch(() => {
                    baAccount.removeToken()
                })
                .finally(() => {
                    state.loading = false
                })
        }
    }
)
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
    .user-info {
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
        a {
            margin-right: 15px;
        }
    }
}

.ba-login {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding: 20px;
    .ba-title {
        width: 100%;
        text-align: center;
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
