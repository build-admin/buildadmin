<template>
    <!-- Icon -->
    <Icon class="ba-icon-dark" v-if="field.render == 'icon'" :name="fieldValue ? fieldValue : field.default ?? ''" />

    <!-- switch -->
    <el-switch
        v-if="field.render == 'switch'"
        @change="onChangeField"
        :model-value="fieldValue.toString()"
        :loading="row.loading"
        active-value="1"
        inactive-value="0"
    />

    <!-- image -->
    <div v-if="field.render == 'image' && fieldValue" class="ba-render-image">
        <el-image
            :hide-on-click-modal="true"
            :preview-teleported="true"
            :preview-src-list="[fullUrl(fieldValue)]"
            :src="fullUrl(fieldValue)"
        ></el-image>
    </div>

    <!-- images -->
    <div v-if="field.render == 'images'" class="ba-render-image">
        <template v-if="Array.isArray(fieldValue) && fieldValue.length">
            <el-image
                v-for="(item, idx) in fieldValue"
                :key="idx"
                :initial-index="idx"
                :preview-teleported="true"
                :preview-src-list="arrayFullUrl(fieldValue)"
                class="images-item"
                :src="fullUrl(item)"
                :hide-on-click-modal="true"
            ></el-image>
        </template>
    </div>

    <!-- tag -->
    <div v-if="field.render == 'tag' && fieldValue !== ''">
        <el-tag :type="getTagType(fieldValue, field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">{{
            field.replaceValue ? field.replaceValue[fieldValue] : fieldValue
        }}</el-tag>
    </div>

    <!-- tags -->
    <div v-if="field.render == 'tags'">
        <template v-if="Array.isArray(fieldValue)">
            <template v-for="(tag, idx) in fieldValue" :key="idx">
                <el-tag
                    v-if="tag"
                    class="m-10"
                    :type="field.customTagType ?? getTagType(tag, field.custom)"
                    :effect="field.effect ?? 'light'"
                    :size="field.size ?? 'default'"
                >
                    {{ field.replaceValue ? field.replaceValue[tag] ?? tag : tag }}</el-tag
                >
            </template>
        </template>
        <template v-else>
            <el-tag
                class="m-10"
                v-if="fieldValue !== ''"
                :type="field.customTagType ?? getTagType(fieldValue, field.custom)"
                :effect="field.effect ?? 'light'"
                :size="field.size ?? 'default'"
            >
                {{ field.replaceValue ? field.replaceValue[fieldValue] ?? fieldValue : fieldValue }}</el-tag
            >
        </template>
    </div>

    <!-- url -->
    <div v-if="field.render == 'url' && fieldValue">
        <el-input :model-value="fieldValue" :placeholder="t('Link address')">
            <template #append>
                <el-button
                    @click="typeof field.click == 'function' ? field.click(row, field, fieldValue, column, index) : openUrl(fieldValue, field)"
                >
                    <Icon :color="'#606266'" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>

    <!-- datetime -->
    <div v-if="field.render == 'datetime'">
        {{ !fieldValue ? '-' : timeFormat(fieldValue, field.timeFormat ?? undefined) }}
    </div>

    <!-- color -->
    <div v-if="field.render == 'color'">
        <div :style="{ background: fieldValue }" class="ba-render-color"></div>
    </div>

    <!-- customTemplate 自定义模板 -->
    <div
        v-if="field.render == 'customTemplate'"
        v-html="field.customTemplate ? field.customTemplate(row, field, fieldValue, column, index) : ''"
    ></div>

    <!-- 自定义组件/函数渲染 -->
    <component
        v-if="field.render == 'customRender'"
        :is="field.customRender"
        :renderRow="row"
        :renderField="field"
        :renderValue="fieldValue"
        :renderColumn="column"
        :renderIndex="index"
    />

    <!-- 按钮组 -->
    <!-- 只对默认的编辑、删除、排序按钮进行鉴权，其他按钮请通过 display 属性控制按钮是否显示 -->
    <div v-if="field.render == 'buttons' && field.buttons">
        <template v-for="(btn, idx) in field.buttons" :key="idx">
            <template v-if="btn.display ? btn.display(row, field) : true">
                <!-- 常规按钮 -->
                <el-button
                    v-if="btn.render == 'basicButton'"
                    v-blur
                    @click="onButtonClick(btn)"
                    :class="btn.class"
                    class="table-operate"
                    :type="btn.type"
                    :disabled="btn.disabled && btn.disabled(row, field)"
                    v-bind="btn.attr"
                >
                    <Icon :name="btn.icon" />
                    <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                </el-button>

                <!-- 带提示信息的按钮 -->
                <el-tooltip
                    v-if="btn.render == 'tipButton' && ((btn.name == 'edit' && baTable.auth('edit')) || btn.name != 'edit')"
                    :disabled="btn.title && !btn.disabledTip ? false : true"
                    :content="btn.title ? t(btn.title) : ''"
                    placement="top"
                >
                    <el-button
                        v-blur
                        @click="onButtonClick(btn)"
                        :class="btn.class"
                        class="table-operate"
                        :type="btn.type"
                        :disabled="btn.disabled && btn.disabled(row, field)"
                        v-bind="btn.attr"
                    >
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                </el-tooltip>

                <!-- 带确认框的按钮 -->
                <el-popconfirm
                    v-if="btn.render == 'confirmButton' && ((btn.name == 'delete' && baTable.auth('del')) || btn.name != 'delete')"
                    :disabled="btn.disabled && btn.disabled(row, field)"
                    v-bind="btn.popconfirm"
                    @confirm="onButtonClick(btn)"
                >
                    <template #reference>
                        <div class="ml-6">
                            <el-tooltip :disabled="btn.title ? false : true" :content="btn.title ? t(btn.title) : ''" placement="top">
                                <el-button
                                    v-blur
                                    :class="btn.class"
                                    class="table-operate"
                                    :type="btn.type"
                                    :disabled="btn.disabled && btn.disabled(row, field)"
                                    v-bind="btn.attr"
                                >
                                    <Icon :name="btn.icon" />
                                    <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>

                <!-- 带提示的可拖拽按钮 -->
                <el-tooltip
                    v-if="btn.render == 'moveButton' && ((btn.name == 'weigh-sort' && baTable.auth('sortable')) || btn.name != 'weigh-sort')"
                    :disabled="btn.title && !btn.disabledTip ? false : true"
                    :content="btn.title ? t(btn.title) : ''"
                    placement="top"
                >
                    <el-button
                        :class="btn.class"
                        class="table-operate move-button"
                        :type="btn.type"
                        :disabled="btn.disabled && btn.disabled(row, field)"
                        v-bind="btn.attr"
                    >
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                </el-tooltip>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import type { TagProps, TableColumnCtx } from 'element-plus'
import { openUrl } from '/@/components/table'
import { useI18n } from 'vue-i18n'
import { fullUrl, arrayFullUrl, timeFormat } from '/@/utils/common'
import type baTableClass from '/@/utils/baTable'

const { t } = useI18n()
const baTable = inject('baTable') as baTableClass

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}
const props = defineProps<Props>()

// 字段值（单元格值）
const fieldName = ref(props.field.prop)
const fieldValue = ref(fieldName.value ? props.row[fieldName.value] : '')
if (fieldName.value && fieldName.value.indexOf('.') > -1) {
    let fieldNameArr = fieldName.value.split('.')
    let val: any = ref(props.row[fieldNameArr[0]])
    for (let index = 1; index < fieldNameArr.length; index++) {
        val.value = val.value ? val.value[fieldNameArr[index]] ?? '' : ''
    }
    fieldValue.value = val.value
}

if (props.field.renderFormatter && typeof props.field.renderFormatter == 'function') {
    fieldValue.value = props.field.renderFormatter(props.row, props.field, fieldValue.value, props.column, props.index)
}

const onChangeField = (value: any) => {
    baTable.onTableAction('field-change', { value: value, ...props })
}

const onButtonClick = (btn: OptButton) => {
    if (typeof btn.click === 'function') {
        btn.click(props.row, props.field)
        return
    }
    baTable.onTableAction(btn.name, props)
}

const getTagType = (value: string, custom: any): TagProps['type'] => {
    return custom && custom[value] ? custom[value] : ''
}
</script>

<style scoped lang="scss">
.m-10 {
    margin: 4px;
}
.ba-render-image {
    text-align: center;
}
.images-item {
    width: 50px;
    margin: 0 5px;
}
.el-image {
    height: 36px;
    width: 36px;
}
.table-operate-text {
    padding-left: 5px;
}
.table-operate {
    padding: 4px 5px;
    height: auto;
}
.table-operate .icon {
    font-size: 14px !important;
    color: var(--ba-bg-color-overlay) !important;
}
.move-button {
    cursor: move;
}
.ml-6 {
    display: inline-flex;
    vertical-align: middle;
    margin-left: 6px;
}
.ml-6 + .el-button {
    margin-left: 6px;
}
.ba-render-color {
    height: 25px;
    width: 100%;
}
</style>
