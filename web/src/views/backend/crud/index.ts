import { reactive } from 'vue'

export const state: {
    step: 'Start' | 'Design'
    type: string
    startData: {
        db: string
        sql: string
        logId: string
    }
} = reactive({
    step: 'Start',
    type: '',
    startData: {
        db: '',
        sql: '',
        logId: '',
    },
})

export const changeStep = (type: string) => {
    state.type = type
    if (type == 'start') {
        state.step = 'Start'
        for (const key in state.startData) {
            state.startData[key as keyof typeof state.startData] = ''
        }
    } else {
        state.step = 'Design'
    }
}

export interface FieldItem {
    title: string
    name: string
    type: string
    dataType?: string
    length: number
    precision: number
    default: string
    null: boolean
    primaryKey: boolean
    unsigned: boolean
    autoIncrement: boolean
    comment: string
    designType: string
    formBuildExclude?: boolean
    tableBuildExclude?: boolean
    table: anyObj
    form: anyObj
}

export const fieldItem: {
    common: FieldItem[]
    base: FieldItem[]
    senior: FieldItem[]
} = {
    common: [
        {
            title: '主键',
            name: 'id',
            type: 'int',
            length: 10,
            precision: 0,
            default: '',
            null: false,
            primaryKey: true,
            unsigned: true,
            autoIncrement: true,
            comment: 'ID',
            designType: 'pk',
            formBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '权重（拖拽排序）',
            name: 'weigh',
            type: 'int',
            length: 10,
            precision: 0,
            default: '',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '权重',
            designType: 'weigh',
            table: {},
            form: {},
        },
        {
            title: '状态',
            name: 'status',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '1',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: '状态:0=禁用,1=启用',
            designType: 'switch',
            table: {},
            form: {},
        },
        {
            title: '备注',
            name: 'remark',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '备注',
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '更新时间',
            name: 'update_time',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: '更新时间',
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '创建时间',
            name: 'create_time',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: '创建时间',
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '远程下拉（关联表）',
            name: 'user_id',
            type: 'int',
            length: 10,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: '远程下拉',
            designType: 'remoteSelect',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
    ],
    base: [
        {
            title: '字符串',
            name: 'string',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '字符串',
            designType: 'string',
            table: {},
            form: {},
        },
        {
            title: '图片',
            name: 'image',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '图片',
            designType: 'image',
            table: {},
            form: {},
        },
        {
            title: '文件',
            name: 'file',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '文件',
            designType: 'file',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '单选框',
            name: 'radio',
            type: 'enum',
            dataType: "enum('opt0','opt1')",
            length: 0,
            precision: 0,
            default: 'opt0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '单选框:opt0=选项一,opt1=选项二',
            designType: 'radio',
            table: {},
            form: {},
        },
        {
            title: '复选框',
            name: 'checkbox',
            type: 'set',
            dataType: "set('opt0','opt1')",
            length: 0,
            precision: 0,
            default: 'opt0,opt1',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '复选框:opt0=选项一,opt1=选项二',
            designType: 'checkbox',
            table: {},
            form: {},
        },
        {
            title: '下拉框',
            name: 'select',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '下拉框:0=选项一,1=选项二',
            designType: 'select',
            table: {},
            form: {},
        },
        {
            title: '开关',
            name: 'switch',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '1',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: '开关:0=关,1=开',
            designType: 'switch',
            table: {},
            form: {},
        },
        {
            title: '富文本',
            name: 'editor',
            type: 'text',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '富文本',
            designType: 'editor',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '多行文本框',
            name: 'textarea',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '多行文本框',
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '整数',
            name: 'number',
            type: 'int',
            length: 10,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '整数',
            designType: 'number',
            table: {},
            form: {},
        },
        {
            title: '浮点数',
            name: 'number',
            type: 'decimal',
            length: 5,
            precision: 2,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '浮点数',
            designType: 'number',
            table: {},
            form: {},
        },
        {
            title: '密码',
            name: 'password',
            type: 'varchar',
            length: 32,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '密码',
            designType: 'password',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '日期',
            name: 'date',
            type: 'date',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '日期',
            designType: 'date',
            table: {},
            form: {},
        },
        {
            title: '时间',
            name: 'time',
            type: 'time',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '时间',
            designType: 'time',
            table: {},
            form: {},
        },
        {
            title: '时间日期',
            name: 'datetime',
            type: 'datetime',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '时间日期',
            designType: 'datetime',
            table: {},
            form: {},
        },
        {
            title: '年份',
            name: 'year',
            type: 'year',
            length: 4,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '年份',
            designType: 'year',
            table: {},
            form: {},
        },
        {
            title: '时间日期（时间戳存储）',
            name: 'timestamp',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '时间日期',
            designType: 'timestamp',
            table: {},
            form: {},
        },
    ],
    senior: [
        {
            title: '数组',
            name: 'array',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '数组',
            designType: 'array',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: '城市选择',
            name: 'city',
            type: 'varchar',
            length: 100,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '城市选择',
            designType: 'city',
            table: {},
            form: {},
        },
        {
            title: '图标选择',
            name: 'icon',
            type: 'varchar',
            length: 50,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: '图标选择',
            designType: 'icon',
            table: {},
            form: {},
        },
    ],
}

const tableBaseAttr = {
    render: {
        type: 'select',
        value: 'none',
        options: {
            none: '无',
            icon: '字体图标(icon)',
            switch: '开关',
            image: '图片',
            images: '多图',
            tag: '标签(tag)',
            tags: '多标签(tags)',
            url: 'URL',
            datetime: '时间日期',
        },
    },
    operator: {
        type: 'select',
        value: '=',
        options: {
            false: '禁用搜索',
            '=': '等于',
            '<>': '不等于',
            '>': '大于',
            '>=': '大于等于',
            '<': '小于',
            '<=': '小于等于',
            LIKE: '模糊查询(LIKE-包含)',
            'NOT LIKE': '模糊查询(NOT LIKE-不包含)',
            IN: 'IN',
            'NOT IN': 'NOT IN',
            RANGE: '范围查询(RANGE)',
            'NOT RANGE': '范围查询(NOT RANGE)',
            NULL: 'NULL',
            'NOT NULL': 'NOT NULL',
            FIND_IN_SET: 'FIND_IN_SET',
        },
    },
    sortable: {
        type: 'select',
        value: 'false',
        options: {
            false: '禁用',
            custom: '启用',
        },
    },
}

const formBaseAttr = {
    validator: {
        type: 'selects',
        value: [],
        options: {
            required: '必填',
            mobile: '手机号',
            account: '账户',
            password: '密码',
            varName: '变量名',
            editorRequired: '富文本必填',
            number: '数字',
            integer: '整数',
            float: '浮点数',
            date: '日期',
            url: 'URL',
            email: '邮箱地址',
        },
    },
    validatorMsg: {
        type: 'textarea',
        value: '',
        placeholder: '留空则自动填写验证器title属性(看不懂请直接填写完整错误消息)',
        attr: {
            rows: 3,
        },
    },
}

const getTableAttr = (type: keyof typeof tableBaseAttr, val: string) => {
    return {
        ...tableBaseAttr[type],
        value: val,
    }
}

const getFormAttr = (type: keyof typeof formBaseAttr, val: string[]) => {
    return {
        ...formBaseAttr[type],
        value: val,
    }
}

export const designTypes: anyObj = {
    pk: {
        name: '主键',
        table: {
            width: {
                type: 'number',
                value: 70,
            },
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: {},
    },
    weigh: {
        name: '权重（自动生成拖拽排序按钮）',
        table: {
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: formBaseAttr,
    },
    timestamp: {
        name: '时间日期（时间戳存储）',
        table: {
            render: getTableAttr('render', 'datetime'),
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
            width: {
                type: 'number',
                value: 160,
            },
            timeFormat: {
                type: 'string',
                value: 'yyyy-mm-dd hh:MM:ss',
            },
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['date']),
        },
    },
    string: {
        name: '字符串',
        table: {
            ...tableBaseAttr,
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: formBaseAttr,
    },
    password: {
        name: '密码',
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['password']),
        },
    },
    number: {
        name: '数字',
        table: {
            ...tableBaseAttr,
            operator: getTableAttr('operator', 'RANGE'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['number']),
            step: {
                type: 'number',
                value: 1,
            },
        },
    },
    radio: {
        name: '单选',
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tag'),
        },
        form: formBaseAttr,
    },
    checkbox: {
        name: '复选',
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tags'),
        },
        form: formBaseAttr,
    },
    switch: {
        name: '开关',
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'switch'),
        },
        form: formBaseAttr,
    },
    textarea: {
        name: '多行文本',
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            rows: {
                type: 'number',
                value: 3,
            },
        },
    },
    array: {
        name: '数组',
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: formBaseAttr,
    },
    datetime: {
        name: '时间日期选择',
        table: {
            operator: getTableAttr('operator', '='),
            sortable: getTableAttr('sortable', 'custom'),
            width: {
                type: 'number',
                value: 160,
            },
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['date']),
        },
    },
    year: {
        name: '年份选择',
        table: {
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['date']),
        },
    },
    date: {
        name: '日期选择',
        table: {
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['date']),
        },
    },
    time: {
        name: '时间选择',
        table: {
            operator: getTableAttr('operator', '='),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: formBaseAttr,
    },
    select: {
        name: '下拉选择',
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tags'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: false,
            },
        },
    },
    remoteSelect: {
        name: '远程下拉选择',
        table: {
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: false,
            },
            'remote-pk': {
                type: 'string',
                value: 'id',
            },
            'remote-field': {
                type: 'string',
                value: 'name',
            },
            'remote-table': {
                type: 'string',
                value: '',
            },
            'remote-controller': {
                type: 'string',
                value: '',
            },
            'remote-model': {
                type: 'string',
                value: '',
            },
            'relation-fields': {
                type: 'string',
                value: '',
            },
            'remote-url': {
                type: 'string',
                value: '',
                placeholder: '不输入则以控制器自动解析',
            },
        },
    },
    editor: {
        name: '富文本',
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['editorRequired']),
        },
    },
    city: {
        name: '城市选择',
        table: {
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: formBaseAttr,
    },
    image: {
        name: '图片上传',
        table: {
            render: getTableAttr('render', 'image'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'image-multi': {
                type: 'switch',
                value: false,
            },
        },
    },
    file: {
        name: '文件上传',
        table: {
            render: getTableAttr('render', 'none'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'file-multi': {
                type: 'switch',
                value: false,
            },
        },
    },
    icon: {
        name: '字体图标选择',
        table: {
            render: getTableAttr('render', 'icon'),
            operator: getTableAttr('operator', 'false'),
        },
        form: formBaseAttr,
    },
}

export const tableFieldsKey = ['quickSearchField', 'formFields', 'columnFields']
