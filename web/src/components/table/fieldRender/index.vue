<template>
    <!-- Icon -->
    <Icon v-if="field.render == 'icon'" :name="row.icon" />

    <!-- switch -->
    <el-switch
        v-if="field.render == 'switch'"
        @change="changeField($event, field.render!, property)"
        :model-value="row.status"
        active-value="1"
        inactive-value="0"
    />

    <!-- image -->
    <div v-if="field.render == 'image'" class="ba-render-image">
        <img src="~assets/bg.jpg" />
    </div>

    <!-- images -->
    <div v-if="field.render == 'images'" class="ba-render-image">
        <img class="image-item" src="~assets/bg.jpg" />
        <img class="image-item" src="~assets/bg.jpg" />
    </div>

    <!-- tag -->
    <div v-if="field.render == 'tag'">tag渲染</div>

    <!-- url -->
    <div v-if="field.render == 'url'">url渲染</div>

    <!-- datetime -->
    <div v-if="field.render == 'datetime'">datetime渲染</div>

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

const changeField = (value: any, type: string, field: keyof TableRow) => {
    if (type == 'switch') {
        props.row[field] = value
    }
}
</script>

<style scoped lang="scss">
.ba-render-image {
    display: flex;
    align-items: center;
    justify-content: center;
    .image-item {
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
