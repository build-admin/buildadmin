<template>
    <div class="login">
        <el-container class="is-vertical">
            <Header />
            <el-main>
                <el-row justify="center">
                    <el-col :span="16" :xs="24">
                        <div class="login-box">
                            <div class="login-title">{{ state.form.tab == 'register' ? '注册' : '登录' }}到{{ siteConfig.site_name }}</div>
                            <el-form>
                                <el-form-item v-if="state.form.tab == 'register'" prop="email">
                                    <el-input v-model="state.form.email" placeholder="请输入邮箱" :clearable="true" size="large">
                                        <template #prefix>
                                            <Icon name="fa fa-envelope" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item prop="username">
                                    <el-input
                                        v-model="state.form.username"
                                        :placeholder="state.form.tab == 'register' ? '请输入用户名' : '请输入用户名/邮箱/手机号'"
                                        :clearable="true"
                                        size="large"
                                    >
                                        <template #prefix>
                                            <Icon name="fa fa-user" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item prop="password">
                                    <el-input v-model="state.form.password" placeholder="请输入账户密码" type="password" show-password size="large">
                                        <template #prefix>
                                            <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item v-if="state.form.tab == 'register'" prop="mobile">
                                    <el-input v-model="state.form.mobile" placeholder="请输入手机号" :clearable="true" size="large">
                                        <template #prefix>
                                            <Icon name="fa fa-tablet" size="16" color="var(--el-input-icon-color)" />
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <el-form-item prop="captcha">
                                    <el-row class="w100">
                                        <el-col :span="16">
                                            <el-input
                                                v-model="state.form.captcha"
                                                clearable
                                                autocomplete="off"
                                                :placeholder="'请输入验证码'"
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
                                                :src="buildCaptchaUrl() + '?id=' + state.captchaId"
                                                alt=""
                                            />
                                        </el-col>
                                    </el-row>
                                </el-form-item>

                                <div v-if="state.form.tab != 'register'" class="form-footer">
                                    <el-checkbox v-model="state.form.keep" label="记住我" size="default"></el-checkbox>
                                    <div @click="state.showRetrievePasswordDialog = true" class="forgot-password">忘记密码？</div>
                                </div>
                                <el-form-item class="form-buttons">
                                    <el-button :loading="state.formLoading" round type="primary" size="large">
                                        {{ state.form.tab == 'register' ? '注册' : '登录' }}
                                    </el-button>
                                    <el-button
                                        v-if="state.form.tab == 'register'"
                                        @click="state.form.tab = 'login'"
                                        round
                                        plain
                                        type="info"
                                        size="large"
                                        >回到登录</el-button
                                    >
                                    <el-button v-else @click="state.form.tab = 'register'" round plain type="info" size="large"
                                        >还没有账户？点击注册</el-button
                                    >
                                </el-form-item>
                            </el-form>
                        </div>
                    </el-col>
                </el-row>
            </el-main>
            <Footer />
        </el-container>

        <el-dialog
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            v-model="state.showRetrievePasswordDialog"
            title="找回密码"
            :width="state.dialogWidth + '%'"
            :draggable="true"
        >
            <div class="retrieve-password-form">
                <el-form :model="state.retrievePasswordForm" :label-width="100">
                    <el-form-item prop="type" label="找回方式">
                        <el-radio-group v-model="state.retrievePasswordForm.type">
                            <el-radio label="email" border>通过邮箱</el-radio>
                            <el-radio label="mobile" disabled border>通过手机号</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item prop="account" :label="state.retrievePasswordForm.type == 'email' ? '邮箱' : '手机号'">
                        <el-input
                            v-model="state.retrievePasswordForm.account"
                            :placeholder="'请输入' + (state.retrievePasswordForm.type == 'email' ? '邮箱' : '手机号')"
                            :clearable="true"
                        >
                            <template #prefix>
                                <Icon name="fa fa-user" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="captcha" label="验证码">
                        <el-row class="w100">
                            <el-col :span="16">
                                <el-input v-model="state.retrievePasswordForm.captcha" placeholder="请输入验证码" autocomplete="off" clearable>
                                    <template #prefix>
                                        <Icon name="fa fa-ellipsis-h" size="16" color="var(--el-input-icon-color)" />
                                    </template>
                                </el-input>
                            </el-col>
                            <el-col class="captcha-box" :span="8">
                                <el-button type="primary">发送</el-button>
                            </el-col>
                        </el-row>
                    </el-form-item>
                    <el-form-item label="新密码">
                        <el-input v-model="state.retrievePasswordForm.password" placeholder="请输入新密码" show-password :clearable="true">
                            <template #prefix>
                                <Icon name="fa fa-unlock-alt" size="16" color="var(--el-input-icon-color)" />
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary">确定</el-button>
                        <el-button @click="state.showRetrievePasswordDialog = false">取消</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted } from 'vue'
import Header from '/@/layouts/frontend/components/header.vue'
import Footer from '/@/layouts/frontend/components/footer.vue'
import { useSiteConfig } from '/@/stores/siteConfig'
import { buildCaptchaUrl } from '/@/api/common'
import { uuid } from '/@/utils/random'

const siteConfig = useSiteConfig()
const state = reactive({
    form: {
        tab: 'login',
        email: '',
        mobile: '',
        username: '',
        password: '',
        captcha: '',
        keep: false,
    },
    formLoading: false,
    showCaptcha: false,
    captchaId: uuid(),
    showRetrievePasswordDialog: false,
    retrievePasswordForm: {
        type: 'email',
        account: '',
        captcha: '',
        password: '',
    },
    dialogWidth: 36,
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
    state.captchaId = uuid()
}
onMounted(() => {
    resize()
    window.addEventListener('resize', resize)
})
onUnmounted(() => {
    window.removeEventListener('resize', resize)
})
</script>

<style scoped lang="scss">
.login-box {
    width: 460px;
    margin: 40px auto;
    padding: 10px 60px 70px 60px;
    background-color: #fff;
}
.login-title {
    text-align: center;
    font-size: var(--el-font-size-large);
    line-height: 100px;
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
.form-footer {
    display: flex;
    align-items: center;
    .forgot-password {
        color: var(--color-primary-sub-0);
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
        margin-right: 0px;
    }
}
</style>
