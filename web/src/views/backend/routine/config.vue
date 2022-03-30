<template>
    <div class="default-main">
        <el-row :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="12">
                <el-form ref="formRef" v-model="state.config" label-position="top">
                    <el-tabs type="border-card">
                        <el-tab-pane v-for="(group, key) in state.config" :key="key" :label="group.title">
                            <!-- <FormItem :items="group.list" /> -->
                            <el-button @click="onSubmit(formRef)">提交</el-button>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
            <el-col :xs="24" :sm="10">
                <el-card header="模块的配置入口将会显示在这里"> 模块配置 </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
// import FormItem from '/@/components/formItem/index.vue'
import { index, postData } from '/@/api/backend/routine/config'
import type { ElForm } from 'element-plus'

const formRef = ref<InstanceType<typeof ElForm>>()

const state: {
    config: anyObj
    remark: string
} = reactive({
    config: [],
    remark: '',
})

const getIndex = () => {
    index().then((res) => {
        state.config = res.data.list
        state.remark = res.data.remark
    })
}

const onSubmit = (formEl: InstanceType<typeof ElForm> | undefined) => {}

onMounted(() => {
    getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'routine/config',
})
</script>

<style scoped lang="scss">
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--color-bg-1);
    border-bottom: none;
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-card :deep(.el-card__header) {
    height: 40px;
    padding: 0;
    line-height: 40px;
    border: none;
    padding-left: 20px;
    background-color: #f5f5f5;
}
@media screen and (max-width: 768px) {
    .xs-mb-20 {
        margin-bottom: 20px;
    }
}
</style>
