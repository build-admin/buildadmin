<template>
    <!-- Icon -->
    <Icon v-if="field.render == 'icon'" :name="row[property] ? row[property] : field.default ?? ''" />

    <!-- switch -->
    <el-switch
        v-if="field.render == 'switch'"
        @change="changeField($event, property)"
        :model-value="row[property]"
        active-value="1"
        inactive-value="0"
    />

    <!-- image -->
    <div v-if="field.render == 'image'" class="ba-render-image">
        <img src="~assets/bg.jpg" />
    </div>

    <!-- images -->
    <div v-if="field.render == 'images'" class="ba-render-image">
        <img class="images-item" src="~assets/bg.jpg" />
        <img class="images-item" src="~assets/bg.jpg" />
    </div>

    <!-- tag -->
    <div v-if="field.render == 'tag'">
        <el-tag :type="getTagType(row[property], field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">{{
            field.replaceValue ? field.replaceValue[row[property]] : row[property]
        }}</el-tag>
    </div>

    <!-- url -->
    <div v-if="field.render == 'url'">
        <el-input :model-value="row[property]" placeholder="链接地址">
            <template #append>
                <el-button @click="typeof field.click == 'function' ? field.click(row[property], field) : openUrl(row[property], field)">
                    <Icon :color="'#606266'" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>

    <!-- datetime -->
    <div v-if="field.render == 'datetime'">
        {{ timeFormat(row[property], field.timeFormat ?? undefined) }}
    </div>

    <!-- 按钮组 -->
    <div v-if="field.render == 'buttons' && field.buttons">
        <template v-for="(btn, idx) in field.buttons">
            <el-tooltip :disabled="btn.title ? false : true" :content="btn.title ?? ''" placement="top">
                <el-button @click="btn.click(btn.name, row, field)" :class="btn.class" class="table-operate" :type="btn.type">
                    <Icon :name="btn.icon" />
                    <div v-if="btn.text" class="table-operate-text">{{ btn.text }}</div>
                </el-button>
            </el-tooltip>
        </template>
    </div>
</template>

<script setup lang="ts">
import type { TagProps } from 'element-plus'
import { timeFormat, openUrl } from '/@/components/table'
import useCurrentInstance from '/@/utils/useCurrentInstance'

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

const changeField = (value: any, fieldName: keyof TableRow) => {
    if (props.field.render == 'switch') {
        props.row[fieldName] = value
        proxy.eventBus.emit('onTableFieldChange', {
            value: value,
            row: props.row,
            field: fieldName,
            render: props.field.render,
        })
    }
}

const getTagType = (value: string, custom: any): TagProps['type'] => {
    return custom && custom[value] ? custom[value] : ''
}
</script>

<style scoped lang="scss">
.ba-render-image {
    text-align: center;
    .images {
        display: block;
    }
    .images-item {
        margin: 0 5px;
    }
    img {
        height: 36px;
        width: 36px;
    }
}
.table-operate-text {
    padding-left: 5px;
}
</style>
