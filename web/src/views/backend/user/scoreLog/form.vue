<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
    >
        <template #title>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <FormItem
                        type="remoteSelect"
                        prop="user_id"
                        label="会员ID"
                        v-model="baTable.form.items!.user_id"
                        placeholder="点击选择"
                        :input-attr="{
                            pk: 'user.id',
                            field: 'nickname_text',
                            'remote-url': userUser + 'index',
                            onChange: getAdd,
                        }"
                    />
                    <el-form-item label="用户名">
                        <el-input v-model="state.userInfo.username" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="用户昵称">
                        <el-input v-model="state.userInfo.nickname" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="当前积分">
                        <el-input v-model="state.userInfo.score" disabled type="number"></el-input>
                    </el-form-item>
                    <el-form-item prop="score" label="变动数额">
                        <el-input @input="changeScore" v-model="baTable.form.items!.score" type="number" placeholder="请输入积分变更数额"></el-input>
                    </el-form-item>
                    <el-form-item label="变更后积分">
                        <el-input v-model="state.after" type="number" disabled></el-input>
                    </el-form-item>
                    <el-form-item prop="memo" label="备注">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.memo"
                            type="textarea"
                            placeholder="请输入变更备注/说明"
                        ></el-input>
                    </el-form-item>
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">取消</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds!.length > 1 ? '保存并编辑下一项' : '保存' }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import type { ElForm } from 'element-plus'
import { add } from '/@/api/backend/user/scoreLog'
import { userUser } from '/@/api/controllerUrls'
import FormItem from '/@/components/formItem/index.vue'
import type { FormItemRule } from 'element-plus'

const baTable = inject('baTable') as baTableClass
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    user_id: [
        {
            required: true,
            message: '请选择用户',
            trigger: 'blur',
        },
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val || parseInt(val) <= 0) {
                    return callback(new Error('请选择用户'))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    score: [
        {
            required: true,
            message: '请输入正确的变更数额',
            trigger: 'blur',
        },
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val || parseInt(val) == 0) {
                    return callback(new Error('请输入正确的变更数额'))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    memo: [
        {
            required: true,
            message: '请输入备注',
            trigger: 'blur',
        },
    ],
})

const { t } = useI18n()
const formRef = ref<InstanceType<typeof ElForm>>()

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
        state.after = res.data.user.score
    })
}

const changeScore = (value: string) => {
    if (!state.userInfo || typeof state.userInfo == 'undefined') {
        state.after = 0
        return
    }
    let newValue = value == '' ? 0 : parseFloat(value)
    state.after = parseFloat(state.userInfo.score) + newValue
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
