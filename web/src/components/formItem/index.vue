<template>
    <template v-for="(item, idx) in props.items" :key="idx">
        <el-form-item v-if="item.type == 'string' || item.type == 'number'" :label="item.title">
            <el-input :type="item.type" :placeholder="item.tip" v-model="item.value"></el-input>
        </el-form-item>
        <el-form-item v-else-if="item.type == 'radio'">
            <template #label>
                <div class="label-box">
                    <div>{{ item.title }}</div>
                    <div class="label-tip">{{ item.tip }}</div>
                </div>
            </template>
            <el-radio v-for="(r, rk) in item.content" v-model="item.value" :label="rk" size="large">{{ r }}</el-radio>
        </el-form-item>
        <el-form-item v-else-if="item.type == 'checkbox'">
            <template #label>
                <div class="label-box">
                    <div>{{ item.title }}</div>
                    <div class="label-tip">{{ item.tip }}</div>
                </div>
            </template>
            <el-checkbox v-for="(c, ck) in item.content" v-model="item.content[ck]" :label="ck" size="large"></el-checkbox>
        </el-form-item>
        <el-form-item v-else-if="item.type == 'switch'">
            <template #label>
                <div class="label-box">
                    <div>{{ item.title }}</div>
                    <div class="label-tip">{{ item.tip }}</div>
                </div>
            </template>
            <el-switch v-model="item.value" />
        </el-form-item>
        <el-form-item v-else>
            <div class="label-not-support">暂不支持 {{ item.type }} 类型的表单组件，可自行于 `/@/src/components/formItem` 中增加逻辑</div>
        </el-form-item>
    </template>
</template>

<script lang="ts" setup>
interface Props {
    items: FormItemProps[]
}
const props = withDefaults(defineProps<Props>(), {
    items: () => [],
})
</script>

<style lang="scss" scoped>
.label-box {
    display: flex;
    align-items: flex-end;
    .label-tip {
        padding-left: 6px;
        font-size: 12px;
        color: var(--color-secondary);
    }
}
.label-not-support {
    line-height: 15px;
}
</style>
