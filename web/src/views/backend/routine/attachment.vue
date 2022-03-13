<template>
    <div class="default-main">
        <div class="ba-table-box">
            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'edit', 'delete']"
                :enable-batch-opt="table.selection.length > 0 ? true : false"
                :quick-search-placeholder="'通过原始名称搜索'"
                @action="onAction"
            />
            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table
                ref="tableRef"
                :data="table.data"
                :field="table.column"
                :row-key="table.pk"
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
                    <el-form @keyup.enter="onSubmit" v-model="form.items" label-position="right" :label-width="form.labelWidth + 'px'">
                        <el-form-item label="细目">
                            <el-input v-model="form.items.topic" type="string" placeholder="文件保存目录，修改记录不会自动转移文件"></el-input>
                        </el-form-item>
                        <el-form-item label="物理路径">
                            <el-input v-model="form.items.url" type="string" placeholder="文件保存路径，修改记录不会自动转移文件"></el-input>
                        </el-form-item>
                        <el-form-item label="图片宽度">
                            <el-input v-model="form.items.width" type="number" placeholder="图片文件的宽度"></el-input>
                        </el-form-item>
                        <el-form-item label="图片高度">
                            <el-input v-model="form.items.height" type="number" placeholder="图片文件的高度"></el-input>
                        </el-form-item>
                        <el-form-item label="原始名称">
                            <el-input v-model="form.items.name" type="string" placeholder="文件原始名称"></el-input>
                        </el-form-item>
                        <el-form-item label="文件大小">
                            <el-input v-model="form.items.size" type="number" placeholder="文件大小(bytes)"></el-input>
                        </el-form-item>
                        <el-form-item label="mime类型">
                            <el-input v-model="form.items.mimetype" type="string" placeholder="文件mime类型"></el-input>
                        </el-form-item>
                        <el-form-item label="上传(引用)次数">
                            <el-input v-model="form.items.quote" type="number" placeholder="此文件的上传(引用)次数"></el-input>
                            <span class="block-help">同一文件被多次上传时，只会保存一份和增加一条附件记录</span>
                        </el-form-item>
                        <el-form-item label="存储方式">
                            <el-input v-model="form.items.storage" type="string" placeholder="存储方式"></el-input>
                        </el-form-item>
                        <el-form-item label="sha1编码">
                            <el-input v-model="form.items.sha1" type="string" placeholder="文件的sha1编码"></el-input>
                        </el-form-item>
                    </el-form>
                </div>
                <template #footer>
                    <div :style="'width: calc(100% - ' + form.labelWidth / 1.8 + 'px)'">
                        <el-button @click="toggleForm('')">取消</el-button>
                        <el-button v-blur :loading="form.submitLoading" @click="onSubmit" type="primary">
                            {{ form.operateIds.length > 1 ? '保存并编辑下一项' : '保存' }}
                        </el-button>
                    </div>
                </template>
            </el-dialog>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { index, edit, del, postData } from '/@/api/backend/routine/Attachment'
import { adminBuildSuffixSvgUrl } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import { useI18n } from 'vue-i18n'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { getArrayKey } from '/@/utils/common'

const { t } = useI18n()
const { proxy } = useCurrentInstance()

/* 表格状态-s */
const tableRef = ref()
const opButtons = defaultOptButtons()
const sortButtonsKey = getArrayKey(opButtons, 'name', 'weigh-sort')
opButtons.splice(sortButtonsKey, 1)
const table: {
    // 主键字段
    pk: string
    // 数据源
    data: TableRow[]
    // 选中项
    selection: TableRow[]
    // 不需要'双击编辑'的字段
    dblClickNotEditColumn: (string | undefined)[]
    // 列数据
    column: TableColumn[]
} = reactive({
    pk: 'id',
    data: [],
    selection: [],
    dblClickNotEditColumn: [undefined],
    column: [
        { type: 'selection', align: 'center' },
        { label: '细目', prop: 'topic', align: 'center' },
        { label: '上传用户', prop: 'admin_id', align: 'center' },
        {
            label: '大小',
            prop: 'size',
            align: 'center',
            formatter: (row: TableRow, column: TableColumn, cellValue: string, index: number) => {
                var size = parseFloat(cellValue)
                var i = Math.floor(Math.log(size) / Math.log(1024))
                return parseInt((size / Math.pow(1024, i)).toFixed(i < 2 ? 0 : 2)) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i]
            },
        },
        { label: '类型', prop: 'mimetype', align: 'center' },
        {
            label: '预览',
            prop: 'suffix',
            align: 'center',
            renderFormatter: (row: TableRow, column: TableColumn, cellValue: string) => {
                let imgSuffix = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']
                if (imgSuffix.includes(cellValue)) {
                    return row.full_url
                }
                return adminBuildSuffixSvgUrl(cellValue)
            },
            render: 'image',
        },
        { label: '上传(引用)次数', prop: 'quote', align: 'center', width: 120 },
        { label: '原始名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
        { label: '存储方式', prop: 'storage', align: 'center', width: 100 },
        { label: '物理路径', prop: 'url', align: 'center', 'show-overflow-tooltip': true, width: 160 },
        { label: '最后上传时间', prop: 'lastuploadtime', align: 'center', render: 'datetime', width: 160 },
        {
            label: '操作',
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: opButtons,
        },
    ],
})
/* 表格状态-e */

/* 表单状态-s */
const form: {
    labelWidth: number
    operate: string
    operateIds: string[]
    items: anyObj
    submitLoading: boolean
    defaultItems: anyObj
} = reactive({
    // 表单label宽度
    labelWidth: 160,
    // 当前操作:add=添加,edit=编辑
    operate: '',
    // 被操作数据ID,支持批量编辑:add=[0],edit=[1,2,n]
    operateIds: [],
    // 表单数据
    items: {},
    submitLoading: false,
    // 默认表单数据(添加)
    defaultItems: {},
})
/* 表单状态-e */

/* API请求方法-s */
const getIndex = (loading: boolean = false, filter: anyObj = {}) => {
    index(loading, filter).then((res) => {
        table.data = res.data.list
    })
}
// 发送删除请求
const postDel = (ids: string[]) => {
    del(ids).then((res) => {
        onAction('refresh', {})
    })
}
// 请求编辑数据
const requestEdit = (id: string) => {
    form.items = {}
    edit({
        id: id,
    }).then((res) => {
        form.items = res.data.row
    })
}
/* API请求方法-e */

/**
 * 接受并记录用户选择的表格项
 * @param selection 被选择项
 */
const onTableSelection = (selection: TableRow[]) => {
    table.selection = selection
}

/**
 * 双击表格
 */
const onTableDblclick = (row: TableRow, column: any) => {
    if (table.dblClickNotEditColumn.indexOf(column.property) === -1) {
        toggleForm('edit', [row[table.pk]])
    }
}

/**
 * 打开表单
 * @param operate 操作:add=添加,edit=编辑
 * @param operateIds 被操作项的数组:add=[],edit=[1,2,...]
 */
const toggleForm = (operate: string = '', operateIds: string[] = []) => {
    if (operate == 'edit') {
        if (!operateIds.length) {
            return false
        }
        requestEdit(operateIds[0])
    } else if (operate == 'add') {
        form.items = form.defaultItems
    }
    form.operate = operate
    form.operateIds = operateIds
}

/**
 * 提交表单
 */
const onSubmit = () => {
    form.submitLoading = true
    postData(form.operate, form.items)
        .then((res) => {
            onAction('refresh', {})
            form.submitLoading = false
            form.operateIds.shift()
            if (form.operateIds.length > 0) {
                toggleForm('edit', form.operateIds)
            } else {
                toggleForm()
            }
        })
        .catch((err) => {
            form.submitLoading = false
        })
}

/* 获取表格选择项的id数组 */
const getSelectionIds = () => {
    let ids: string[] = []
    for (const key in table.selection) {
        ids.push(table.selection[key][table.pk])
    }
    return ids
}

/**
 * 表格顶栏按钮响应
 * @param type 点击的按钮
 * @param data 携带数据
 */
const onAction = (type: string, data: anyObj) => {
    const actionFun = new Map([
        [
            'refresh',
            () => {
                table.data = []
                getIndex(data.loading ? true : false)
            },
        ],
        [
            'edit',
            () => {
                toggleForm('edit', getSelectionIds())
            },
        ],
        [
            'delete',
            () => {
                postDel(getSelectionIds())
            },
        ],
        [
            'quick-search',
            () => {
                getIndex(true, {
                    keyword: data.keyword,
                })
            },
        ],
    ])

    let action = actionFun.get(type) || actionFun.get('default')
    return action!.call(this)
}

onMounted(() => {
    getIndex(false, {})

    /**
     * 表格内的按钮响应
     * @param name 按钮name
     * @param row 被操作行数据
     */
    proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
        if (data.name == 'edit') {
            toggleForm('edit', [data.row[table.pk]])
        } else if (data.name == 'delete') {
            postDel([data.row[table.pk]])
        }
    })
})
onUnmounted(() => {
    proxy.eventBus.off('onTableButtonClick')
})
</script>

<style scoped lang="scss"></style>
