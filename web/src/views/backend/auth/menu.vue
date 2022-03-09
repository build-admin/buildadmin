<template>
    <div class="default-main">
        <div class="ba-table-box">
            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
                :enable-batch-opt="table.selection.length > 0 ? true : false"
                :unfold="table.expandAll"
                @action="onAction"
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
            <el-dialog
                custom-class="ba-operate-dialog"
                top="10vh"
                :close-on-click-modal="false"
                :model-value="form.operate ? true : false"
                @close="toggleForm"
            >
                <template #title>
                    <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                        {{ form.operate ? t(form.operate) : '' }}
                    </div>
                </template>
                <div class="ba-operate-form" :class="'ba-' + form.operate + '-form'" :style="'width: calc(100% - ' + form.labelWidth / 2 + 'px)'">
                    <el-form v-model="form.items" label-position="right" :label-width="form.labelWidth + 'px'">
                        <el-form-item label="上级菜单规则">
                            <remoteSelect
                                :params="{ isTree: true }"
                                field="title"
                                :remote-url="indexUrl"
                                v-model="form.items.pid"
                                placeholder="点击选择"
                            />
                        </el-form-item>
                        <el-form-item label="规则类型">
                            <el-radio v-model="form.items.type" label="menu_dir" :border="true">菜单目录</el-radio>
                            <el-radio v-model="form.items.type" label="menu" :border="true">菜单项</el-radio>
                            <el-radio v-model="form.items.type" label="button" :border="true">页面按钮</el-radio>
                        </el-form-item>
                        <el-form-item label="规则标题">
                            <el-input v-model="form.items.title" type="string" placeholder="请输入菜单规则标题"></el-input>
                        </el-form-item>
                        <el-form-item label="规则名称">
                            <el-input v-model="form.items.name" type="string" placeholder="英文名称，无需以`/admin`开头，如:auth/menu"></el-input>
                            <div class="block-help">将注册为web端路由名称，同时作为server端API验权使用</div>
                        </el-form-item>
                        <el-form-item v-if="form.items.type != 'button'" label="路由路径">
                            <el-input
                                v-model="form.items.path"
                                type="string"
                                placeholder="web端路由路径(path)，无需以`/admin`开头，如:auth/menu"
                            ></el-input>
                        </el-form-item>
                        <el-form-item v-if="form.items.type != 'button'" label="规则图标">
                            <IconSelector v-model="form.items.icon" />
                        </el-form-item>
                        <el-form-item v-if="form.items.type == 'menu'" label="菜单类型">
                            <el-radio v-model="form.items.menu_type" label="tab" :border="true">选项卡</el-radio>
                            <el-radio v-model="form.items.menu_type" label="link" :border="true">链接(站外)</el-radio>
                            <el-radio v-model="form.items.menu_type" label="iframe" :border="true">Iframe</el-radio>
                        </el-form-item>
                        <el-form-item v-if="form.items.menu_type != 'tab'" label="链接地址">
                            <el-input v-model="form.items.url" type="string" placeholder="请输入链接或Iframe的URL地址"></el-input>
                        </el-form-item>
                        <el-form-item v-if="form.items.type == 'menu'" label="组件路径">
                            <el-input
                                v-model="form.items.component"
                                type="string"
                                placeholder="web端组件路径，请以/src开头，如:/src/views/backend/dashboard.vue"
                            ></el-input>
                        </el-form-item>
                        <el-form-item v-if="form.items.type == 'menu'" label="扩展属性">
                            <el-select class="w100" v-model="form.items.extend" clearable placeholder="请选择扩展属性">
                                <el-option label="只添加为路由" value="add_rules_only"></el-option>
                                <el-option label="只添加为菜单" value="add_menu_only"></el-option>
                            </el-select>
                            <div class="block-help">
                                比如将`auth/menu`只添加为路由，那么可以另外将`auth/menu`、`auth/menu/:a`、`auth/menu/:b/:c`只添加为菜单
                            </div>
                        </el-form-item>
                        <el-form-item label="规则备注">
                            <el-input
                                v-model="form.items.remark"
                                type="textarea"
                                :autosize="{ minRows: 2, maxRows: 5 }"
                                placeholder="在控制器中使用`get_route_remark()`函数，可以获得此字段值自用，比如控制台的banner文案"
                            ></el-input>
                        </el-form-item>
                        <el-form-item label="规则权重">
                            <el-input v-model="form.items.weigh" type="number" placeholder="请输入菜单规则权重(排序依据)"></el-input>
                        </el-form-item>
                        <el-form-item label="状态">
                            <el-radio v-model="form.items.status" label="0" :border="true">禁用</el-radio>
                            <el-radio v-model="form.items.status" label="1" :border="true">启用</el-radio>
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
import { indexUrl, index, edit, postEdit } from '/@/api/backend/auth/Menu'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import IconSelector from '/@/components/icon/selector.vue'
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

const getIndex = (loading: boolean = false) => {
    index(loading).then((res) => {
        table.data = res.data.menu
    })
}

const requestEdit = (id: string) => {
    edit({
        id: id,
    }).then((res) => {
        form.items = res.data.row
    })
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
    if (operate == 'edit') {
        if (!operateIds.length) {
            return false
        }
        requestEdit(operateIds[0])
    } else if (operate == 'add') {
        form.items = {}
    }
    form.operate = operate
    form.operateIds = operateIds
}

const onSubmit = () => {
    // 待完善
    // 保存时去除当前编辑的记录
    // 加载下一个记录
    // 若无记录，关闭dialog
    console.log(form.items)
}

const onAction = (type: string, data: anyObj) => {
    const actionFun = new Map([
        [
            'refresh',
            () => {
                getIndex(true)
            },
        ],
        [
            'add',
            () => {
                toggleForm('add')
            },
        ],
        [
            'edit',
            () => {
                let ids: string[] = []
                for (const key in table.selection) {
                    ids.push(table.selection[key][table.pk])
                }
                toggleForm('edit', ids)
            },
        ],
        [
            'delete',
            () => {
                console.log('执行删除')
            },
        ],
        [
            'unfold',
            () => {
                table.expandAll = data.unfold
                tableRef.value.unFoldAll(data.unfold)
            },
        ],
        [
            'default',
            () => {
                console.log('未定义操作')
            },
        ],
    ])

    let action = actionFun.get(type) || actionFun.get('default')
    return action!.call(this)
}

getIndex()
</script>

<style lang="scss" scoped></style>
