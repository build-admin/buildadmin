<template>
    <div>
        <el-dialog
            class="ba-operate-dialog manage-operate-dialog"
            :close-on-click-modal="false"
            :model-value="baTable.form.extend!.showManage"
            @close="baTable.form.extend!.showManage = false"
            width="50%"
            :destroy-on-close="true"
        >
            <template #header>
                <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                    模块管理 - {{ baTable.form.extend!.info.title }}
                </div>
            </template>
            <el-scrollbar class="ba-table-form-scrollbar">
                <div class="ba-operate-form" :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                    <el-form
                        ref="formRef"
                        @keyup.enter="onSubmit"
                        :model="state.items"
                        label-position="right"
                        :label-width="baTable.form.labelWidth + 'px'"
                        :rules="state.rules"
                    >
                        <FormItem
                            label="操作"
                            v-model="state.items.opt"
                            type="radio"
                            class="form-item-opts"
                            :data="{
                                content: {
                                    install_mode: '安装模式切换',
                                    install_sql: '安装sql调试',
                                    exec_function: '模块核心控制器方法调试',
                                },
                                childrenAttr: { border: true },
                            }"
                        />
                        <el-form-item label="操作说明" v-if="['install_sql', 'install_mode'].includes(state.items.opt)">
                            <div v-if="state.items.opt == 'install_sql'" class="install-tip-box">
                                点击执行操作将找到模块根目录/install.sql，并执行其中的sql查询（同模块安装时一致，无该文件或查询出错不会抛出异常）。
                            </div>
                            <div v-if="state.items.opt == 'install_mode'" class="install-tip-box">
                                <div>
                                    <span>模块有两种安装模式：</span>
                                    <p>1、完整安装：模块中需要安装至系统的代码文件将<b>复制</b>到系统之中</p>
                                    <p>2、纯净安装：模块中需要安装至系统的代码文件将<b>移动</b>到系统之中</p>
                                </div>
                                <div>
                                    <span>当前模块的安装模式为：</span>
                                    <b>{{ baTable.form.extend!.info.install_mode == 'full' ? '完整' : '纯净' }}</b>
                                    <span>，点击执行操作将切换为{{ baTable.form.extend!.info.install_mode == 'full' ? '纯净' : '完整' }}安装</span>
                                </div>
                            </div>
                        </el-form-item>
                        <FormItem
                            v-if="state.items.opt == 'exec_function'"
                            label="调用方法"
                            v-model="state.items.fun"
                            type="select"
                            :data="{
                                content: { install: '安装', uninstall: '卸载', enable: '启用', disable: '禁用', update: '更新' },
                                childrenAttr: { border: true },
                            }"
                            :attr="{
                                blockHelp:
                                    '只调用模块核心控制器内对应方法代码，并不等同于真正的模块安装/卸载等操作（找不到方法不会抛出异常，但代码错误可以抛出异常）',
                            }"
                        />
                    </el-form>
                </div>
            </el-scrollbar>
            <template #footer>
                <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                    <el-button @click="baTable.form.extend!.showManage = false">关闭</el-button>
                    <el-button v-blur :loading="state.submitLoading" @click="onSubmit" type="primary"> 执行操作 </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, inject } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import type baTableClass from '/@/utils/baTable'
import { manage } from '/@/api/backend/modulesdev'

const baTable = inject('baTable') as baTableClass

const state: {
    items: anyObj
    rules: anyObj
    submitLoading: boolean
} = reactive({
    items: {
        opt: 'install_mode',
        fun: 'install',
    },
    rules: {},
    submitLoading: false,
})

const onSubmit = () => {
    state.submitLoading = true
    manage({
        ...state.items,
        uid: baTable.form.extend!.info.uid,
        mode: baTable.form.extend!.info.install_mode == 'full' ? 'pure' : 'full',
    })
        .then(() => {
            baTable.form.extend!.showManage = false
            baTable.onTableHeaderAction('refresh', {})
        })
        .finally(() => {
            state.submitLoading = false
        })
}
</script>

<style scoped lang="scss">
.install-tip-box {
    line-height: 20px;
    font-size: 13px;
}
:deep(.manage-operate-dialog) {
    .el-dialog__body {
        height: unset;
    }
}
:deep(.form-item-opts) {
    .el-radio {
        margin-bottom: 10px;
    }
}
</style>
