<template>
    <!-- Icon -->
    <Icon class="ba-icon-dark" v-if="field.render == 'icon'" :name="fieldValue ? fieldValue : field.default ?? ''" />

    <!-- switch -->
    <el-switch
        v-if="field.render == 'switch'"
        @change="changeField($event, property)"
        :model-value="fieldValue.toString()"
        :loading="row.loading"
        active-value="1"
        inactive-value="0"
    />

    <!-- image -->
    <div v-if="field.render == 'image' && fieldValue" class="ba-render-image">
        <el-image :preview-teleported="true" :preview-src-list="[fullUrl(fieldValue)]" :src="fullUrl(fieldValue)"></el-image>
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
                <el-tag v-if="tag" class="m-10" :type="getTagType(tag, field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">{{
                    field.replaceValue ? field.replaceValue[tag] ?? tag : tag
                }}</el-tag>
            </template>
        </template>
        <template v-else>
            <el-tag class="m-10" v-if="fieldValue !== ''" :type="getTagType(fieldValue, field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">{{
                field.replaceValue ? field.replaceValue[fieldValue] ?? fieldValue : fieldValue
            }}</el-tag>
        </template>
    </div>

    <!-- url -->
    <div v-if="field.render == 'url' && fieldValue">
        <el-input :model-value="fieldValue" :placeholder="t('Link address')">
            <template #append>
                <el-button @click="typeof field.click == 'function' ? field.click(fieldValue, field) : openUrl(fieldValue, field)">
                    <Icon :color="'#606266'" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>

    <!-- datetime -->
    <div v-if="field.render == 'datetime'">
        {{ !fieldValue ? '-' : timeFormat(fieldValue, field.timeFormat ?? undefined) }}
    </div>

    <!-- customTemplate 自定义模板 -->
    <div v-if="field.render == 'customTemplate'" v-html="field.customTemplate ? field.customTemplate(row, field, fieldValue) : ''"></div>

    <!-- 自定义组件/函数渲染 -->
    <component v-if="field.render == 'customRender'" :is="field.customRender" :renderRow="row" :renderField="field" :renderValue="fieldValue" />

    <!-- 按钮组 -->
    <div v-if="field.render == 'buttons' && field.buttons">
        <template v-for="(btn, idx) in field.buttons" :key="idx">
            <template v-if="btn.display ? btn.display(row, field) : true">
                <el-tooltip
                    v-if="btn.render == 'tipButton'"
                    :disabled="btn.title ? false : true"
                    :content="btn.title ? t(btn.title) : ''"
                    placement="top"
                >
                    <el-button
                        v-if="btn.name == 'edit'"
                        v-auth="'edit'"
                        v-blur
                        @click="onButtonClick(btn.name)"
                        :class="btn.class"
                        class="table-operate"
                        :type="btn.type"
                    >
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                    <el-button v-else v-blur @click="onButtonClick(btn.name)" :class="btn.class" class="table-operate" :type="btn.type">
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                </el-tooltip>
                <el-popconfirm v-if="btn.render == 'confirmButton'" v-bind="btn.popconfirm" @confirm="onButtonClick(btn.name)">
                    <template #reference>
                        <div class="ml-6">
                            <el-tooltip :disabled="btn.title ? false : true" :content="btn.title ? t(btn.title) : ''" placement="top">
                                <el-button
                                    v-if="btn.name == 'delete'"
                                    v-auth="'del'"
                                    v-blur
                                    :class="btn.class"
                                    class="table-operate"
                                    :type="btn.type"
                                >
                                    <Icon :name="btn.icon" />
                                    <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                                </el-button>
                                <el-button v-else v-blur :class="btn.class" class="table-operate" :type="btn.type">
                                    <Icon :name="btn.icon" />
                                    <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>
                <el-tooltip
                    v-if="btn.render == 'moveButton'"
                    :disabled="btn.title && !btn.disabledTip ? false : true"
                    :content="btn.title ? t(btn.title) : ''"
                    placement="top"
                >
                    <el-button
                        v-if="btn.name == 'weigh-sort'"
                        v-auth="'sortable'"
                        :class="btn.class"
                        class="table-operate move-button"
                        :type="btn.type"
                    >
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                    <el-button v-else v-blur :class="btn.class" class="table-operate move-button" :type="btn.type">
                        <Icon :name="btn.icon" />
                        <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                    </el-button>
                </el-tooltip>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { TagProps } from 'element-plus'
import { timeFormat, openUrl } from '/@/components/table'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { useI18n } from 'vue-i18n'
import { fullUrl, arrayFullUrl } from '/@/utils/common'

const { t } = useI18n()
const { proxy } = useCurrentInstance()

interface Props {
    row: TableRow
    field: TableColumn
    // 字段名
    property?: string
}

const props = withDefaults(defineProps<Props>(), {
    row: () => [],
    field: () => {
        return {}
    },
    property: '',
})

// 字段值(单元格值)
const fieldValue = ref(props.row[props.property])
if (props.property.indexOf('.') > -1) {
    let fieldNameArr = props.property.split('.')
    let val: any = ref(props.row[fieldNameArr[0]])
    for (let index = 1; index < fieldNameArr.length; index++) {
        val.value = val.value ? val.value[fieldNameArr[index]] ?? '' : ''
    }
    fieldValue.value = val.value
}

if (props.field.renderFormatter && typeof props.field.renderFormatter == 'function') {
    fieldValue.value = props.field.renderFormatter(props.row, props.field, fieldValue.value)
}

const changeField = (value: any, fieldName: keyof TableRow) => {
    if (props.field.render == 'switch') {
        proxy.eventBus.emit('onTableFieldChange', {
            value: value,
            row: props.row,
            field: fieldName,
            render: props.field.render,
        })
    }
}

const onButtonClick = (name: string) => {
    proxy.eventBus.emit('onTableButtonClick', {
        name: name,
        row: props.row,
    })
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
</style>
