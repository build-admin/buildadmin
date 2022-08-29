<template>
    <div class="install-done">
        <div class="install-done-title">
            <span v-if="state.install.state == moduleInstallState.INSTALLED">恭喜，模块安装已完成。</span>
            <span v-else>恭喜，模块的代码已经准备好了。</span>
        </div>
        <div class="install-tis-box">
            <div v-if="state.install.state == moduleInstallState.DEPENDENT_WAIT_INSTALL" class="depend-box">
                <div class="depend-loading" v-if="state.install.dependInstallState == 'executing'" v-loading="true"></div>
                <div class="depend-tis">
                    <div v-if="state.install.dependInstallState == 'executing'">
                        <span class="color-red">请勿刷新页面！</span>本模块添加了新的依赖项，系统内置终端正在自动安装这些依赖，请稍等~
                        <span class="span-a" @click="showTerminal">查看进度</span>
                    </div>
                    <div v-if="state.install.dependInstallState == 'success'" class="color-green">依赖已安装完成~</div>
                    <div v-if="state.install.dependInstallState == 'fail'" class="exec-fail color-red">
                        依赖安装失败，请点击终端重试按钮，您也可以查看<el-link
                            target="_blank"
                            type="primary"
                            href="https://wonderful-code.gitee.io/guide/install/manualOperation.html"
                            >手动完成未尽事宜</el-link
                        >
                    </div>
                </div>
            </div>
            <div v-else class="depend-tis">本模块没有添加新的依赖项。</div>
        </div>
        <div class="install-tis-box">
            <div class="install-tis">请在安装结束后手动的清理系统和浏览器缓存，并刷新页面。</div>
        </div>
        <div class="install-tis-box">
            <div class="install-form">
                <FormItem
                    :label="'安装结束后自动执行重新发布命令？'"
                    v-model="form.rebuild"
                    type="radio"
                    :data="{ content: { 0: '否', 1: '是' }, childrenAttr: { border: true } }"
                />
            </div>
        </div>
        <el-button
            v-blur
            class="install-done-button"
            :disabled="state.install.dependInstallState != 'executing' || state.install.state == moduleInstallState.INSTALLED ? false : true"
            size="large"
            type="primary"
            @click="onSubmitInstallDone"
            >安装结束</el-button
        >
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { state } from '../index'
import { moduleInstallState } from '../types'
import { useTerminal } from '/@/stores/terminal'
import FormItem from '/@/components/formItem/index.vue'
import { taskStatus } from '/@/components/terminal/constant'

const terminal = useTerminal()
const form = reactive({
    rebuild: 0,
})

const showTerminal = () => {
    terminal.toggle(true)
}

const onSubmitInstallDone = () => {
    state.install.showDialog = false
    if (form.rebuild == 1) {
        terminal.toggle(true)
        terminal.addTaskPM('web-build', false, '', (res: number) => {
            if (res == taskStatus.Success) {
                terminal.toggle(false)
            }
        })
    }
}
</script>

<style scoped lang="scss">
.install-done-title {
    font-size: var(--el-font-size-extra-large);
    color: var(--el-color-success);
    text-align: center;
}
.install-tis-box {
    padding: 20px;
    margin: 20px auto;
    width: 60%;
    border: 1px solid var(--el-border-color-lighter);
    border-radius: var(--el-border-radius-base);
    display: flex;
    align-items: center;
    justify-content: center;
}
.depend-box {
    display: flex;
    align-items: center;
    justify-content: center;
}
.install-tis {
    color: var(--el-color-warning);
}
.depend-loading {
    width: 30px;
    height: 30px;
    margin-right: 36px;
}
.span-a {
    color: var(--el-color-primary);
    cursor: pointer;
}
.install-form :deep(.ba-input-item-radio) {
    margin-bottom: 0;
}
.exec-fail {
    display: flex;
}
.color-red {
    color: var(--el-color-danger);
}
.color-green {
    color: var(--el-color-success);
}
</style>
