<template>
    <!-- Icon -->
    <Icon v-if="field.render == 'icon'" :name="fieldValue ? fieldValue : field.default ?? ''" />

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
    <div v-if="field.render == 'image'" class="ba-render-image">
        <el-image v-if="fieldValue" :preview-teleported="true" :preview-src-list="[fieldValue]" :src="fieldValue"></el-image>
    </div>

    <!-- images -->
    <div v-if="field.render == 'images'" class="ba-render-image">
        <template v-if="Array.isArray(fieldValue) && fieldValue.length">
            <el-image
                v-for="(item, idx) in fieldValue"
                :initial-index="idx"
                :preview-teleported="true"
                :preview-src-list="fieldValue"
                class="images-item"
                :src="item"
            ></el-image>
        </template>
    </div>

    <!-- tag -->
    <div v-if="field.render == 'tag'">
        <el-tag :type="getTagType(fieldValue, field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">{{
            field.replaceValue ? field.replaceValue[fieldValue] : fieldValue
        }}</el-tag>
    </div>

    <!-- tags -->
    <div v-if="field.render == 'tags'">
        <template v-if="Array.isArray(fieldValue)">
            <template v-for="tag in fieldValue">
                <el-tag v-if="tag" class="m-10" size="small" effect="light">{{ field.replaceValue ? field.replaceValue[tag] ?? tag : tag }}</el-tag>
            </template>
        </template>
        <template v-else>
            <el-tag class="m-10" v-if="fieldValue" size="small" effect="light">{{
                field.replaceValue ? field.replaceValue[fieldValue] ?? fieldValue : fieldValue
            }}</el-tag>
        </template>
    </div>

    <!-- url -->
    <div v-if="field.render == 'url'">
        <el-input :model-value="fieldValue" placeholder="链接地址">
            <template #append>
                <el-button @click="typeof field.click == 'function' ? field.click(fieldValue, field) : openUrl(fieldValue, field)">
                    <Icon :color="'#606266'" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>

    <!-- datetime -->
    <div v-if="field.render == 'datetime'">
        {{ timeFormat(fieldValue, field.timeFormat ?? undefined) }}
    </div>

    <!-- 按钮组 -->
    <div v-if="field.render == 'buttons' && field.buttons">
        <template v-for="(btn, idx) in field.buttons">
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
                            <el-button v-if="btn.name == 'delete'" v-auth="'del'" v-blur :class="btn.class" class="table-operate" :type="btn.type">
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
                    v-blur
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
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { TagProps } from 'element-plus'
import { timeFormat, openUrl } from '/@/components/table'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { useI18n } from 'vue-i18n'

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
    let val: any = props.row[fieldNameArr[0]]
    for (let index = 1; index < fieldNameArr.length; index++) {
        val = val ? (val[fieldNameArr[index]] ?? ''):''
    }
    fieldValue.value = val
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
    color: #ffffff !important;
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
