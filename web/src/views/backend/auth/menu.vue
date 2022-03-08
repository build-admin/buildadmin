<template>
    <div class="default-main">
        <div class="ba-table-box">
            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
                :enable-batch-opt="table.selection.length > 0 ? true : false"
                :unfold="table.expandAll"
                @on-unfold="onTableUnfold"
            />
            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table
                ref="tableRef"
                :default-expand-all="table.expandAll"
                :data="table.data"
                :field="table.column"
                @selection-change="onTableSelection"
                @row-dblclick="onTableDblclick"
            />
            <!-- 对话框表单 -->
            <el-dialog custom-class="ba-operate-dialog" :close-on-click-modal="false" :model-value="form.operate ? true : false" @close="toggleForm">
                <template #title>
                    <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                        {{ form.operate ? t(form.operate) : '' }}
                    </div>
                </template>
                <div class="ba-operate-form" :class="'ba-' + form.operate + '-form'" :style="'width: calc(100% - ' + form.labelWidth / 2 + 'px)'">
                    <el-form v-model="form.items" label-position="right" :label-width="form.labelWidth + 'px'">
                        <el-form-item label="上级菜单">
                            <remoteSelect
                                :params="{ isTree: true }"
                                field="title"
                                :remote-url="indexUrl"
                                v-model="form.items.pid"
                                placeholder="请输入上级菜单"
                            />
                        </el-form-item>
                    </el-form>
                </div>
                <template #footer>
                    <div :style="'width: calc(100% - ' + form.labelWidth / 1.8 + 'px)'">
                        <el-button @click="toggleForm('')">取消</el-button>
                        <el-button v-blur @click="onSubmit" type="primary">提交</el-button>
                    </div>
                </template>
            </el-dialog>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { indexUrl, index } from '/@/api/backend/auth/Menu'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import remoteSelect from '/@/components/remoteSelect/index.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

/*
 * 表格
 */
const tableRef = ref()
const table: {
    // 主键字段
    pk: string
    // 数据源
    data: TableRow[]
    // 选中项
    selection: TableRow[]
    // 是否展开所有子项
    expandAll: boolean
    // 不需要'双击编辑'的字段
    dblClickNotEditColumn: (string | undefined)[]
    // 列数据
    column: TableColumn[]
} = reactive({
    pk: 'id',
    data: [],
    selection: [],
    expandAll: true,
    dblClickNotEditColumn: [undefined, 'status'],
    column: [
        { type: 'selection', align: 'center' },
        { label: '标题', prop: 'title', align: 'left' },
        { label: '图片', prop: 'title', align: 'left', render: 'image', width: '60' },
        { label: '图片测试', prop: 'title', align: 'left', render: 'images', width: '164' },
        { label: '图标', prop: 'icon', align: 'center', width: '60', render: 'icon' },
        { label: '名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
        {
            label: '类型',
            prop: 'type',
            align: 'center',
            render: 'tag',
            custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
        },
        { label: '组件路径', prop: 'component', align: 'center', 'show-overflow-tooltip': true },
        { label: '状态', prop: 'status', align: 'center', width: '80', render: 'switch' },
        {
            label: '操作',
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: defaultOptButtons(),
        },
    ],
})

/*
 * 表单
 */
const form: {
    labelWidth: number
    operate: string
    operateIds: string[]
    items: anyObj
} = reactive({
    // 表单label宽度
    labelWidth: 160,
    // 当前操作:add=添加,edit=编辑
    operate: '',
    // 被操作数据ID,支持批量编辑:add=[0],edit=[1,2,n]
    operateIds: [],
    items: {},
})

index().then((res) => {
    table.data = res.data.menu
})

/*
 * 表格折叠展开子级
 */
const onTableUnfold = (unfold: boolean) => {
    table.expandAll = unfold
    tableRef.value.unFoldAll(unfold)
}
/*
 * 接受表格中选中的数据
 */
const onTableSelection = (selection: TableRow[]) => {
    table.selection = selection
}

const onTableDblclick = (row: TableRow, column: any) => {
    if (table.dblClickNotEditColumn.indexOf(column.property) === -1) {
        toggleForm('edit', [row[table.pk]])
    }
}

const toggleForm = (operate: string = '', operateIds: string[] = []) => {
    form.operate = operate
    form.operateIds = operateIds
}

const onSubmit = () => {
    console.log(form.items)
}
</script>

<style lang="scss" scoped></style>
