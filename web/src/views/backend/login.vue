<template>
    <div>
        <div class="switch-language">
            <el-dropdown size="large" :hide-timeout="50" placement="bottom-end" :hide-on-click="true">
                <Icon name="fa fa-globe" color="var(--color-secondary)" size="28" />
                <template #dropdown>
                    <el-dropdown-menu class="chang-lang">
                        <el-dropdown-item v-for="item in langArray" :key="item.name" @click="editDefaultLang(item.name)">
                            {{ item.value }}
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
        </div>
        <div @contextmenu.stop="" id="bubble" class="bubble">
            <canvas id="bubble-canvas" class="bubble-canvas"></canvas>
        </div>
        <div class="login">
            <div class="login-box">
                <div class="head">
                    <img src="~assets/login-header.png" alt="" />
                </div>
                <div class="form">
                    <img class="profile-avatar" src="~assets/avatar.png" alt="" />
                    <div class="content">
                        <el-form @keyup.enter="onSubmit(formRef)" ref="formRef" :rules="rules" size="large" :model="form">
                            <el-form-item prop="username">
                                <el-input
                                    ref="username"
                                    type="text"
                                    clearable
                                    v-model="form.username"
                                    :placeholder="t('adminLogin.Please enter an account')"
                                >
                                    <template #prefix>
                                        <Icon name="fa fa-user" class="form-item-icon" size="16" color="var(--el-input-icon-color)" />
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item prop="password">
                                <el-input
                                    ref="password"
                                    v-model="form.password"
                                    type="password"
                                    :placeholder="t('adminLogin.Please input a password')"
                                    show-password
                                >
                                    <template #prefix>
                                        <Icon name="fa fa-unlock-alt" class="form-item-icon" size="16" color="var(--el-input-icon-color)" />
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item v-if="state.showCaptcha" prop="captcha">
                                <el-row class="w100" :gutter="15">
                                    <el-col :span="16">
                                        <el-input
                                            ref="captcha"
                                            type="text"
                                            :placeholder="t('adminLogin.Please enter the verification code')"
                                            v-model="form.captcha"
                                            clearable
                                            autocomplete="off"
                                        >
                                            <template #prefix>
                                                <Icon name="fa fa-ellipsis-h" class="form-item-icon" size="16" color="var(--el-input-icon-color)" />
                                            </template>
                                        </el-input>
                                    </el-col>
                                    <el-col :span="8">
                                        <img @click="onChangeCaptcha" class="captcha-img" :src="buildCaptchaUrl() + '?id=' + state.captchaId" alt="" />
                                    </el-col>
                                </el-row>
                            </el-form-item>
                            <el-checkbox v-model="form.keep" :label="t('adminLogin.Hold session')" size="default"></el-checkbox>
                            <el-form-item>
                                <el-button :loading="form.loading" class="submit-button" round type="primary" size="large" @click="onSubmit(formRef)">
                                    {{ t('adminLogin.Sign in') }}
                                </el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getCurrentInstance, onMounted, onBeforeUnmount, reactive, ref, computed } from 'vue'
import * as pageBubble from '/@/utils/pageBubble'
import type { ElForm } from 'element-plus'
import { useI18n } from 'vue-i18n'
import { editDefaultLang } from '/@/lang/index'
import { useConfig } from '/@/stores/config'
import { useAdminInfo } from '/@/stores/adminInfo'
import { login } from '/@/api/backend'
import { buildCaptchaUrl } from '/@/api/common'
import { uuid } from '../../utils/random'
import { validatorPassword, validatorAccount } from '/@/utils/validate'
import router from '/@/router'
var timer: NodeJS.Timer

const config = useConfig()
const adminInfo = useAdminInfo()

const state = reactive({
    showCaptcha: false,
    captchaId: uuid(),
})

const onChangeCaptcha = () => {
    form.captcha = ''
    state.captchaId = uuid()
}

const formRef = ref<InstanceType<typeof ElForm>>()
const form = reactive({
    username: '',
    password: '',
    captcha: '',
    keep: false,
    loading: false,
    captcha_id: '',
})

const { t } = useI18n()

// 表单验证规则
const rules = reactive({
    username: [
        {
            required: true,
            message: t('adminLogin.Please enter an account'),
            trigger: 'blur',
        },
        {
            validator: validatorAccount,
            trigger: 'blur',
        },
    ],
    password: [
        {
            required: true,
            message: t('adminLogin.Please input a password'),
            trigger: 'blur',
        },
        {
            validator: validatorPassword,
            trigger: 'blur',
        },
    ],
    captcha: [
        {
            required: true,
            message: t('adminLogin.Please enter the verification code'),
            trigger: 'blur',
        },
        {
            min: 4,
            max: 6,
            message: t('adminLogin.The verification code length must be between 4 and 6 bits'),
            trigger: 'blur',
        },
    ],
})

onMounted(() => {
    timer = setTimeout(() => {
        pageBubble.init()
    }, 1000)
    const vm: any = getCurrentInstance()
    if (form.username === '') {
        vm.ctx.$refs.username.focus()
    } else if (form.password === '') {
        vm.ctx.$refs.password.focus()
    } else if (form.captcha === '') {
        vm.ctx.$refs.captcha.focus()
    }

    login('get')
        .then((res) => {
            state.showCaptcha = res.data.captcha
        })
        .catch((err) => {
            console.log(err)
        })
})

onBeforeUnmount(() => {
    clearTimeout(timer)
    pageBubble.removeListeners()
})

const langArray = computed(() => config.lang.langArray)

const onSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            form.loading = true
            form.captcha_id = state.captchaId
            login('post', form)
                .then((res) => {
                    form.loading = false
                    adminInfo.$state = res.data.userinfo
                    router.push({ name: res.data.routeName })
                })
                .catch((err) => {
                    onChangeCaptcha()
                    form.loading = false
                })
        } else {
            onChangeCaptcha()
            return false
        }
    })
}
</script>

<style scoped lang="scss">
.switch-language {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1;
}
.bubble {
    overflow: hidden;
    background: url(/@/assets/bg.jpg) repeat;
}
.form-item-icon {
    height: auto;
}
.login {
    position: absolute;
    top: 0;
    display: flex;
    width: 100vw;
    height: 100vh;
    align-items: flex-start;
    justify-content: center;
    .login-box {
        overflow: hidden;
        width: 430px;
        padding: 0;
        background: #fff;
        margin-top: 130px;
    }
    .head {
        background: #ccccff;
        img {
            display: block;
            width: 430px;
            margin: 0 auto;
            user-select: none;
        }
    }
    .form {
        position: relative;
        .profile-avatar {
            display: block;
            position: absolute;
            height: 100px;
            width: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            top: -50px;
            right: 175px;
            z-index: 2;
            user-select: none;
        }
        .content {
            padding: 100px 40px 40px 40px;
        }
        .submit-button {
            width: 100%;
            letter-spacing: 2px;
            font-weight: 300;
            margin-top: 15px;
        }
    }
}

@media screen and (max-width: 720px) {
    .login {
        display: flex;
        align-items: center;
        justify-content: center;
        .login-box {
            width: 340px;
            margin-top: 0;
        }
    }
}
.chang-lang :deep(.el-dropdown-menu__item) {
    justify-content: center;
}
.content :deep(.el-input__prefix) {
    display: flex;
    align-items: center;
}
.captcha-img {
    width: 100%;
}
</style>
