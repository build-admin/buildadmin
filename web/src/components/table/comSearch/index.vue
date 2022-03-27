<template>
    <transition name="el-fade-in">
        <div class="table-com-search">
            <el-form @keyup.enter="onComSearch" label-position="top" :model="state.form">
                <el-row>
                    <template v-for="(item, idx) in baTable.table.column">
                        <template v-if="item.operator !== false">
                            <el-col v-if="item.render == 'datetime' && (item.operator == 'RANGE' || item.operator == 'NOT RANGE')" :span="12">
                                <div class="com-search-col">
                                    <div class="com-search-col-label w16">{{ item.label }}</div>
                                    <div class="com-search-col-input-range w83">
                                        <el-date-picker
                                            class="datetime-picker"
                                            v-model="state.form[item.prop!]"
                                            :default-value="state.form[item.prop! + '-default'] ? state.form[item.prop! + '-default']:[new Date(), new Date()]"
                                            type="datetimerange"
                                            range-separator="To"
                                            start-placeholder="Start date"
                                            end-placeholder="End date"
                                            value-format="YYYY-MM-DD HH:mm:ss"
                                        />
                                    </div>
                                </div>
                            </el-col>
                            <el-col v-else :span="6">
                                <div class="com-search-col">
                                    <div class="com-search-col-label">{{ item.label }}</div>
                                    <div v-if="item.operator == 'RANGE' || item.operator == 'NOT RANGE'" class="com-search-col-input-range">
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            type="string"
                                            v-model="state.form[item.prop! + '-start']"
                                        ></el-input>
                                        <div class="range-separator">至</div>
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            type="string"
                                            v-model="state.form[item.prop! + '-end']"
                                        ></el-input>
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
                                            :placeholder="item.operatorPlaceholder"
                                            :default-value="state.form[item.prop! + '-default'] ? state.form[item.prop! + '-default']:new Date()"
                                        ></el-date-picker>
                                        <el-select
                                            :placeholder="item.operatorPlaceholder"
                                            v-else-if="item.render == 'tag'"
                                            v-model="state.form[item.prop!]"
                                            :clearable="true"
                                        >
                                            <el-option v-for="(opt, okey) in item.replaceValue" :key="item.prop! + okey" :label="opt" :value="okey" />
                                        </el-select>
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            v-else
                                            type="string"
                                            v-model="state.form[item.prop!]"
                                        ></el-input>
                                    </div>
                                </div>
                            </el-col>
                        </template>
                    </template>
                    <el-col :span="6">
                        <div class="com-search-col pl-20">
                            <el-button v-blur @click="onComSearch" type="primary">搜索</el-button>
                            <el-button @click="onResetForm()">重置</el-button>
                        </div>
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { reactive, inject } from 'vue'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import type baTableClass from '/@/utils/baTable'

const { proxy } = useCurrentInstance()

const baTable = inject('baTable') as baTableClass

// 各个字段要同时发送到后台的数据
const fieldData = baTable.comSearch.fieldData

const state: {
    form: anyObj
} = reactive({
    form: baTable.comSearch.form,
})

const onComSearch = () => {
    let comSearchData: comSearchData[] = []
    for (const key in state.form) {
        if (!fieldData.has(key)) {
            continue
        }

        let val = ''
        let fieldDataTemp = fieldData.get(key)
        if (fieldDataTemp.render == 'datetime' && (fieldDataTemp.operator == 'RANGE' || fieldDataTemp.operator == 'NOT RANGE')) {
            // 时间范围组件返回的是时间数组
            if (state.form[key] && state.form[key].length >= 2) {
                // 数组转字符串，以实现通过url参数传递预设搜索值
                val = state.form[key][0] + ',' + state.form[key][1]
            }
        } else if (fieldDataTemp.operator == 'RANGE' || fieldDataTemp.operator == 'NOT RANGE') {
            // 普通的范围筛选，baTable在初始化时已准备好start和end字段
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
                operator: fieldDataTemp.operator,
                render: fieldDataTemp.render,
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
.w16 {
    width: 16.5% !important;
}
.w83 {
    width: 83.5% !important;
}
</style>
