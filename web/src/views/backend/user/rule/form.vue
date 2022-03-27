<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        top="5vh"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
    >
        <template #title>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
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
            >
                <el-form-item prop="pid" label="上级菜单规则">
                    <remoteSelect
                        :params="{ isTree: true }"
                        field="title"
                        :remote-url="baTable.api.actionUrl.get('index')"
                        v-model="baTable.form.items!.pid"
                        placeholder="点击选择"
                    />
                </el-form-item>
                <el-form-item label="规则类型">
                    <el-radio class="ba-el-radio" v-model="baTable.form.items!.type" label="route" :border="true">普通路由</el-radio>
                    <el-radio class="ba-el-radio" v-model="baTable.form.items!.type" label="menu_dir" :border="true">会员中心菜单目录</el-radio>
                    <el-radio class="ba-el-radio" v-model="baTable.form.items!.type" label="menu" :border="true">会员中心菜单项</el-radio>
                </el-form-item>
                <el-form-item prop="title" label="规则标题">
                    <el-input v-model="baTable.form.items!.title" type="string" placeholder="请输入菜单规则标题"></el-input>
                </el-form-item>
                <el-form-item prop="name" label="规则名称">
                    <el-input v-model="baTable.form.items!.name" type="string" placeholder="英文名称"></el-input>
                    <div class="block-help">将注册为web端路由名称，同时作为server端API验权使用</div>
                </el-form-item>
                <el-form-item v-if="baTable.form.items!.type != 'button'" label="路由路径">
                    <el-input v-model="baTable.form.items!.path" type="string" placeholder="web端路由路径(path)"></el-input>
                </el-form-item>
                <el-form-item v-if="baTable.form.items!.type != 'button'" label="规则图标">
                    <IconSelector :show-icon-name="true" v-model="baTable.form.items!.icon" />
                </el-form-item>
                <el-form-item v-if="baTable.form.items!.type == 'menu'" label="菜单类型">
                    <el-radio v-model="baTable.form.items!.menu_type" label="tab" :border="true">选项卡</el-radio>
                    <el-radio v-model="baTable.form.items!.menu_type" label="link" :border="true">链接(站外)</el-radio>
                    <el-radio v-model="baTable.form.items!.menu_type" label="iframe" :border="true">Iframe</el-radio>
                </el-form-item>
                <el-form-item prop="url" v-if="baTable.form.items!.menu_type != 'tab'" label="链接地址">
                    <el-input v-model="baTable.form.items!.url" type="string" placeholder="请输入链接或Iframe的URL地址"></el-input>
                </el-form-item>
                <el-form-item v-if="baTable.form.items!.type == 'menu' && baTable.form.items!.menu_type == 'tab'" label="组件路径">
                    <el-input
                        v-model="baTable.form.items!.component"
                        type="string"
                        placeholder="web端组件路径，请以/src开头，如:/src/views/backend/dashboard.vue"
                    ></el-input>
                </el-form-item>
                <el-form-item v-if="baTable.form.items!.type == 'menu' && baTable.form.items!.menu_type == 'tab'" label="扩展属性">
                    <el-select class="w100" v-model="baTable.form.items!.extend" placeholder="请选择扩展属性">
                        <el-option label="无" value="none"></el-option>
                        <el-option label="只添加为路由" value="add_rules_only"></el-option>
                        <el-option label="只添加为菜单" value="add_menu_only"></el-option>
                    </el-select>
                    <div class="block-help">
                        比如将`auth/menu`只添加为路由，那么可以另外将`auth/menu`、`auth/menu/:a`、`auth/menu/:b/:c`只添加为菜单
                    </div>
                </el-form-item>
                <el-form-item label="规则备注">
                    <el-input
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        v-model="baTable.form.items!.remark"
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 5 }"
                        placeholder="请输入规则备注"
                    ></el-input>
                </el-form-item>
                <el-form-item label="规则权重">
                    <el-input v-model="baTable.form.items!.weigh" type="number" placeholder="请输入菜单规则权重(排序依据)"></el-input>
                </el-form-item>
                <el-form-item label="缓存">
                    <el-radio v-model="baTable.form.items!.keepalive" :label="0" :border="true">禁用</el-radio>
                    <el-radio v-model="baTable.form.items!.keepalive" :label="1" :border="true">启用</el-radio>
                </el-form-item>
                <el-form-item label="状态">
                    <el-radio v-model="baTable.form.items!.status" label="0" :border="true">禁用</el-radio>
                    <el-radio v-model="baTable.form.items!.status" label="1" :border="true">启用</el-radio>
                </el-form-item>
            </el-form>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">取消</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? '保存并编辑下一项' : '保存' }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import remoteSelect from '/@/components/remoteSelect/index.vue'
import IconSelector from '/@/components/icon/selector.vue'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'
import type { ElForm } from 'element-plus'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [
        {
            required: true,
            message: '请输入规则标题',
            trigger: 'blur',
        },
    ],
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error('上级菜单规则不能是规则本身'))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    name: [
        {
            required: true,
            message: '请输入规则名称',
            trigger: 'blur',
        },
    ],
    url: [
        {
            type: 'url',
            message: '请输入正确的Url',
            trigger: 'blur',
        },
    ],
})
</script>

<style scoped lang="scss">
.ba-el-radio {
    margin-bottom: 10px;
}
</style>
