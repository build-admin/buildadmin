<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <FormItem
                        type="remoteSelect"
                        prop="user_id"
                        :label="t('user.moneyLog.User ID')"
                        v-model="baTable.form.items!.user_id"
                        :placeholder="t('Click select')"
                        :input-attr="{
                            pk: 'user.id',
                            field: 'nickname_text',
                            remoteUrl: '/admin/user.User/index',
                            onChange: getAdd,
                        }"
                    />
                    <el-form-item :label="t('user.moneyLog.User name')">
                        <el-input v-model="state.userInfo.username" disabled></el-input>
                    </el-form-item>
                    <el-form-item :label="t('user.moneyLog.User nickname')">
                        <el-input v-model="state.userInfo.nickname" disabled></el-input>
                    </el-form-item>
                    <el-form-item :label="t('user.moneyLog.Current balance')">
                        <el-input v-model="state.userInfo.money" disabled type="number"></el-input>
                    </el-form-item>
                    <el-form-item prop="money" :label="t('user.moneyLog.Change amount')">
                        <el-input
                            @input="changeMoney"
                            v-model="baTable.form.items!.money"
                            type="number"
                            :placeholder="t('user.moneyLog.Please enter the balance change amount')"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('user.moneyLog.Balance after change')">
                        <el-input v-model="state.after" type="number" disabled></el-input>
                    </el-form-item>
                    <el-form-item prop="memo" :label="t('user.moneyLog.remarks')">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.memo"
                            type="textarea"
                            :placeholder="t('user.moneyLog.Please enter change remarks / description')"
                        ></el-input>
                    </el-form-item>
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds!.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, inject, watch, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import { add } from '/@/api/backend/user/moneyLog'
import FormItem from '/@/components/formItem/index.vue'
import type { FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { useConfig } from '/@/stores/config'

const config = useConfig()
const { t } = useI18n()
const baTable = inject('baTable') as baTableClass
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    user_id: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('user.moneyLog.User') }) })],
    money: [
        buildValidatorData({ name: 'required', title: t('user.moneyLog.Change amount') }),
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val || parseFloat(val) == 0) {
                    return callback(new Error(t('Please enter the correct field', { field: t('user.moneyLog.Change amount') })))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    memo: [buildValidatorData({ name: 'required', title: t('user.moneyLog.remarks') })],
})

const formRef = useTemplateRef('formRef')

const state: {
    userInfo: anyObj
    after: number
} = reactive({
    userInfo: {},
    after: 0,
})

const getAdd = () => {
    if (!baTable.form.items!.user_id || parseInt(baTable.form.items!.user_id) <= 0) {
        return
    }
    add(baTable.form.items!.user_id).then((res) => {
        state.userInfo = res.data.user
        state.after = res.data.user.money
    })
}

const changeMoney = (value: string) => {
    if (!state.userInfo || typeof state.userInfo == 'undefined') {
        state.after = 0
        return
    }
    let newValue = value == '' ? 0 : parseFloat(value)
    state.after = parseFloat((parseFloat(state.userInfo.money) + newValue).toFixed(2))
}

// 打开表单时刷新用户数据
watch(
    () => baTable.form.operate,
    (newValue) => {
        if (newValue) {
            getAdd()
        }
    }
)
</script>

<style scoped lang="scss">
.preview-img {
    width: 60px;
    height: 60px;
}
</style>
