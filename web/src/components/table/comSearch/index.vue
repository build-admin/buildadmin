<template>
    <transition name="el-fade-in">
        <div class="table-com-search">
            <el-form @keyup.enter="onComSearch" label-position="top" :model="state.form">
                <el-row>
                    <template v-for="(item, idx) in field">
                        <el-col v-if="item.operator !== false" :span="6">
                            <div class="com-search-col">
                                <div class="com-search-col-label">{{ item.label }}</div>
                                <div v-if="item.operator == 'RANGE' || item.operator == 'NOT RANGE'" class="com-search-col-input-range">
                                    <el-input type="string" v-model="state.form[item.prop! + '-start']"></el-input>
                                    <div class="range-separator">至</div>
                                    <el-input type="string" v-model="state.form[item.prop! + '-end']"></el-input>
                                </div>
                                <div v-else-if="item.operator == 'NULL' || item.operator == 'NOT NULL'" class="com-search-col-input">
                                    <el-checkbox v-model="state.form[item.prop!]" :label="item.operator" size="large"></el-checkbox>
                                </div>
                                <div v-else-if="item.operator" class="com-search-col-input">
                                    <el-date-picker
                                        class="datetime-picker"
                                        v-if="item.render == 'datetime'"
                                        v-model="state.form[item.prop!]"
                                        type="datetime"
                                        value-format="YYYY-MM-DD HH:mm:ss"
                                    ></el-date-picker>
                                    <el-select v-else-if="item.render == 'tag'" v-model="state.form[item.prop!]">
                                        <el-option v-for="(opt, okey) in item.replaceValue" :key="item.prop! + okey" :label="opt" :value="okey" />
                                    </el-select>
                                    <el-input v-else type="string" v-model="state.form[item.prop!]"></el-input>
                                </div>
                            </div>
                        </el-col>
                    </template>
                    <el-col :span="6">
                        <div class="com-search-col pl-20">
                            <el-button @click="onComSearch" type="primary">搜索</el-button>
                            <el-button @click="onResetForm()">重置</el-button>
                        </div>
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import useCurrentInstance from '/@/utils/useCurrentInstance'

const { proxy } = useCurrentInstance()

// 各个字段的 operator
const operator = new Map()

interface Props {
    field: TableColumn[]
}
const props = withDefaults(defineProps<Props>(), {
    field: () => [],
})

const state: {
    form: anyObj
} = reactive({
    form: {},
})

// 公共搜索字段数据预处理
if (props.field.length > 0) {
    for (const key in props.field) {
        let prop = props.field[key].prop
        if (typeof props.field[key].operator == 'undefined') {
            props.field[key].operator = '='
        }
        if (prop) {
            if (props.field[key].operator == 'RANGE' || props.field[key].operator == 'NOT RANGE') {
                state.form[prop] = ''
                state.form[prop + '-start'] = ''
                state.form[prop + '-end'] = ''
            } else if (props.field[key].operator == 'NULL' || props.field[key].operator == 'NOT NULL') {
                state.form[prop] = false
            } else {
                state.form[prop] = ''
            }
            operator.set(prop, props.field[key].operator)
        }
    }
}

const onComSearch = () => {
    let comSearchData: comSearchData[] = []
    for (const key in state.form) {
        if (!operator.has(key)) {
            continue
        }

        let val = ''
        if (operator.get(key) == 'RANGE' || operator.get(key) == 'NOT RANGE') {
            if (!state.form[key + '-start'] && !state.form[key + '-end']) {
                continue
            }
            val = state.form[key + '-start'] + ',' + state.form[key + '-end']
        } else if (state.form[key]) {
            val = state.form[key]
        }

        if (val) {
            comSearchData.push({
                field: key,
                val: val,
                operator: operator.get(key),
            })
        }
    }

    proxy.eventBus.emit('onTableComSearch', comSearchData)
}

const onResetForm = () => {
    // 封装好的onResetForm在此处不能使用
    for (const key in state.form) {
        state.form[key] = ''
    }
}
</script>

<style scoped lang="scss">
.table-com-search {
    overflow: hidden;
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
    background-color: #ffffff;
    border: 1px solid #f6f6f6;
    border-bottom: none;
    padding: 13px 15px;
    font-size: 14px;
    .com-search-col {
        display: flex;
        align-items: center;
        padding-top: 8px;
        color: var(--color-regular);
        font-size: 13px;
    }
    .com-search-col-label {
        width: 33.33%;
        padding: 0 15px;
        text-align: right;
        overflow: hidden;
        white-space: nowrap;
    }
    .com-search-col-input {
        padding: 0 15px;
        width: 66.66%;
    }
    .com-search-col-input-range {
        display: flex;
        align-items: center;
        padding: 0 15px;
        width: 66.66%;
        .range-separator {
            padding: 0 5px;
        }
    }
}
:deep(.datetime-picker) {
    width: 100%;
}
.pl-20 {
    padding-left: 20px;
}
</style>
