<template>
    <el-row :gutter="10">
        <el-col :span="12" class="ba-array-key">键名</el-col>
        <el-col :span="12" class="ba-array-value">键值</el-col>
    </el-row>
    <el-row class="ba-array-item" v-for="item in state.value" :gutter="10">
        <el-col :span="12">
            <el-input v-model="item.key"></el-input>
        </el-col>
        <el-col :span="12">
            <el-input v-model="item.value"></el-input>
        </el-col>
    </el-row>
    <el-button v-blur class="ba-add-array-item" @click="addArrayItem" icon="el-icon-Plus">添加</el-button>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

type baInputArray = { key: string; value: string }
interface Props {
    modelValue: baInputArray[]
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
})

const state = reactive({
    value: props.modelValue,
})

/* const emits = defineEmits<{
    (e: 'update:modelValue', value: baInputArray[]): void
}>()
const onInput = () => {
    emits('update:modelValue', state.value)
} */

const addArrayItem = () => {
    state.value.push({
        key: '',
        value: '',
    })
    // emits('update:modelValue', state.value)
}
</script>

<style scoped lang="scss">
.ba-array-key,
.ba-array-value {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px 0;
    color: var(--color-secondary);
}
.ba-array-item {
    margin-bottom: 6px;
}
.ba-add-array-item {
    float: right;
    margin-right: 6px;
}
</style>
