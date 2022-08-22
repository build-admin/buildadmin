<template>
    <transition name="el-fade-in">
        <div class="table-com-search">
            <el-form @keyup.enter="onComSearch" label-position="top" :model="baTable.comSearch.form">
                <el-row>
                    <template v-for="(item, idx) in baTable.table.column" :key="idx">
                        <template v-if="item.operator !== false">
                            <!-- 时间范围 -->
                            <el-col v-if="item.render == 'datetime' && (item.operator == 'RANGE' || item.operator == 'NOT RANGE')" :span="12">
                                <div class="com-search-col">
                                    <div class="com-search-col-label w16">{{ item.label }}</div>
                                    <div class="com-search-col-input-range w83">
                                        <el-date-picker
                                            class="datetime-picker"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                            :default-value="baTable.comSearch.form[item.prop! + '-default'] ? baTable.comSearch.form[item.prop! + '-default']:[new Date(), new Date()]"
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
                                    <!-- 数字范围 -->
                                    <div v-if="item.operator == 'RANGE' || item.operator == 'NOT RANGE'" class="com-search-col-input-range">
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            type="string"
                                            v-model="baTable.comSearch.form[item.prop! + '-start']"
                                        ></el-input>
                                        <div class="range-separator">{{ $t('to') }}</div>
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            type="string"
                                            v-model="baTable.comSearch.form[item.prop! + '-end']"
                                        ></el-input>
                                    </div>
                                    <!-- 是否 [NOT] NULL -->
                                    <div v-else-if="item.operator == 'NULL' || item.operator == 'NOT NULL'" class="com-search-col-input">
                                        <el-checkbox v-model="baTable.comSearch.form[item.prop!]" :label="item.operator" size="large"></el-checkbox>
                                    </div>
                                    <div v-else-if="item.operator" class="com-search-col-input">
                                        <!-- 时间筛选 -->
                                        <el-date-picker
                                            class="datetime-picker"
                                            v-if="item.render == 'datetime'"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                            type="datetime"
                                            value-format="YYYY-MM-DD HH:mm:ss"
                                            :placeholder="item.operatorPlaceholder"
                                            :default-value="baTable.comSearch.form[item.prop! + '-default'] ? baTable.comSearch.form[item.prop! + '-default']:new Date()"
                                        ></el-date-picker>

                                        <!-- tag、select -->
                                        <el-select
                                            :placeholder="item.operatorPlaceholder"
                                            v-else-if="(item.render == 'tag' || item.comSearchRender == 'select') && item.replaceValue"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                            :clearable="true"
                                        >
                                            <el-option v-for="(opt, okey) in item.replaceValue" :key="item.prop! + okey" :label="opt" :value="okey" />
                                        </el-select>

                                        <!-- 远程 select -->
                                        <BaInput
                                            v-else-if="item.comSearchRender == 'remoteSelect'"
                                            type="remoteSelect"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                            :attr="item.remote"
                                            :placeholder="item.operatorPlaceholder"
                                        />

                                        <!-- 自定义组件/函数渲染 -->
                                        <component
                                            v-else-if="item.comSearchRender == 'customRender'"
                                            :is="item.comSearchCustomRender"
                                            :renderRow="item"
                                            :renderField="item.prop!"
                                            :renderValue="baTable.comSearch.form[item.prop!]"
                                        />

                                        <!-- 开关 -->
                                        <el-select
                                            :placeholder="item.operatorPlaceholder"
                                            v-else-if="item.render == 'switch'"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                            :clearable="true"
                                        >
                                            <template v-if="!isEmpty(item.replaceValue)">
                                                <el-option
                                                    v-for="(opt, okey) in item.replaceValue"
                                                    :key="item.prop! + okey"
                                                    :label="opt"
                                                    :value="okey"
                                                />
                                            </template>
                                            <template v-else>
                                                <el-option :label="$t('utils.open')" value="1" />
                                                <el-option :label="$t('utils.close')" value="0" />
                                            </template>
                                        </el-select>

                                        <!-- 字符串 -->
                                        <el-input
                                            :placeholder="item.operatorPlaceholder"
                                            v-else
                                            type="string"
                                            v-model="baTable.comSearch.form[item.prop!]"
                                        ></el-input>
                                    </div>
                                </div>
                            </el-col>
                        </template>
                    </template>
                    <el-col :span="6">
                        <div class="com-search-col pl-20">
                            <el-button v-blur @click="onComSearch" type="primary">{{ $t('search') }}</el-button>
                            <el-button @click="onResetForm()">{{ $t('Reset') }}</el-button>
                        </div>
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import type baTableClass from '/@/utils/baTable'
import { isEmpty } from 'lodash'
import BaInput from '/@/components/baInput/index.vue'

const { proxy } = useCurrentInstance()

const baTable = inject('baTable') as baTableClass

const onComSearch = () => {
    let comSearchData: comSearchData[] = []
    for (const key in baTable.comSearch.form) {
        if (!baTable.comSearch.fieldData.has(key)) {
            continue
        }

        let val = ''
        let fieldDataTemp = baTable.comSearch.fieldData.get(key)
        if (fieldDataTemp.render == 'datetime' && (fieldDataTemp.operator == 'RANGE' || fieldDataTemp.operator == 'NOT RANGE')) {
            // 时间范围组件返回的是时间数组
            if (baTable.comSearch.form[key] && baTable.comSearch.form[key].length >= 2) {
                // 数组转字符串，以实现通过url参数传递预设搜索值
                val = baTable.comSearch.form[key][0] + ',' + baTable.comSearch.form[key][1]
            }
        } else if (fieldDataTemp.operator == 'RANGE' || fieldDataTemp.operator == 'NOT RANGE') {
            // 普通的范围筛选，baTable在初始化时已准备好start和end字段
            if (!baTable.comSearch.form[key + '-start'] && !baTable.comSearch.form[key + '-end']) {
                continue
            }
            val = baTable.comSearch.form[key + '-start'] + ',' + baTable.comSearch.form[key + '-end']
        } else if (baTable.comSearch.form[key]) {
            val = baTable.comSearch.form[key]
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
    for (const key in baTable.comSearch.form) {
        baTable.comSearch.form[key] = ''
    }
}
</script>

<style scoped lang="scss">
.table-com-search {
    overflow: hidden;
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
    background-color: var(--ba-bg-color-overlay);
    border: 1px solid var(--ba-border-color);
    border-bottom: none;
    padding: 13px 15px;
    font-size: 14px;
    .com-search-col {
        display: flex;
        align-items: center;
        padding-top: 8px;
        color: var(--el-text-color-regular);
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
