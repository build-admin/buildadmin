import { reactive } from 'vue'
import { i18n } from '/@/lang/index'

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
            title: i18n.global.t('crud.state.Primary key'),
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
            title: i18n.global.t('crud.state.Weight (drag and drop sorting)'),
            name: 'weigh',
            type: 'int',
            length: 10,
            precision: 0,
            default: '',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('weigh'),
            designType: 'weigh',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('state'),
            name: 'status',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '1',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: i18n.global.t('crud.state.Status:0=Disabled,1=Enabled'),
            designType: 'switch',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('crud.state.remarks'),
            name: 'remark',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('crud.state.remarks'),
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('updatetime'),
            name: 'update_time',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: i18n.global.t('updatetime'),
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('createtime'),
            name: 'create_time',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: i18n.global.t('createtime'),
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('crud.state.Remote Select (association table)'),
            name: 'user_id',
            type: 'int',
            length: 10,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: i18n.global.t('utils.remote select'),
            designType: 'remoteSelect',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
    ],
    base: [
        {
            title: i18n.global.t('utils.string'),
            name: 'string',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.string'),
            designType: 'string',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.image'),
            name: 'image',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.image'),
            designType: 'image',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.file'),
            name: 'file',
            type: 'varchar',
            length: 200,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.file'),
            designType: 'file',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.radio'),
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
            comment: i18n.global.t('crud.state.Radio:opt0=Option1,opt1=Option2'),
            designType: 'radio',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.checkbox'),
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
            comment: i18n.global.t('crud.state.Checkbox:opt0=Option1,opt1=Option2'),
            designType: 'checkbox',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.select'),
            name: 'select',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('crud.state.Select:0=Option1,1=Option2'),
            designType: 'select',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.switch'),
            name: 'switch',
            type: 'tinyint',
            length: 1,
            precision: 0,
            default: '1',
            null: false,
            primaryKey: false,
            unsigned: true,
            autoIncrement: false,
            comment: i18n.global.t('crud.state.Switch:0=off,1=on'),
            designType: 'switch',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.rich Text'),
            name: 'editor',
            type: 'text',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.rich Text'),
            designType: 'editor',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.textarea'),
            name: 'textarea',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.textarea'),
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.number'),
            name: 'number',
            type: 'int',
            length: 10,
            precision: 0,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.number'),
            designType: 'number',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.float'),
            name: 'float',
            type: 'decimal',
            length: 5,
            precision: 2,
            default: '0',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.float'),
            designType: 'float',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.password'),
            name: 'password',
            type: 'varchar',
            length: 32,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.password'),
            designType: 'password',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.date'),
            name: 'date',
            type: 'date',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.date'),
            designType: 'date',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.time'),
            name: 'time',
            type: 'time',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.time'),
            designType: 'time',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.time date'),
            name: 'datetime',
            type: 'datetime',
            length: 0,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.time date'),
            designType: 'datetime',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.year'),
            name: 'year',
            type: 'year',
            length: 4,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.year'),
            designType: 'year',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('crud.state.Time date (timestamp storage)'),
            name: 'timestamp',
            type: 'bigint',
            length: 16,
            precision: 0,
            default: 'null',
            null: true,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.time date'),
            designType: 'timestamp',
            table: {},
            form: {},
        },
    ],
    senior: [
        {
            title: i18n.global.t('utils.array'),
            name: 'array',
            type: 'varchar',
            length: 255,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.array'),
            designType: 'array',
            tableBuildExclude: true,
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.city select'),
            name: 'city',
            type: 'varchar',
            length: 100,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.city select'),
            designType: 'city',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.icon select'),
            name: 'icon',
            type: 'varchar',
            length: 50,
            precision: 0,
            default: 'empty string',
            null: false,
            primaryKey: false,
            unsigned: false,
            autoIncrement: false,
            comment: i18n.global.t('utils.icon select'),
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
            none: i18n.global.t('none'),
            icon: 'Icon',
            switch: i18n.global.t('utils.switch'),
            image: i18n.global.t('utils.image'),
            images: i18n.global.t('utils.multi image'),
            tag: 'Tag',
            tags: 'Tags',
            url: 'URL',
            datetime: i18n.global.t('utils.time date'),
        },
    },
    operator: {
        type: 'select',
        value: '=',
        options: {
            false: i18n.global.t('crud.state.Disable Search'),
            '=': '=',
            '<>': '!=',
            '>': '>',
            '>=': '>=',
            '<': '<',
            '<=': '<=',
            LIKE: 'LIKE',
            'NOT LIKE': 'NOT LIKE',
            IN: 'IN',
            'NOT IN': 'NOT IN',
            RANGE: 'RANGE',
            'NOT RANGE': 'NOT RANGE',
            NULL: 'NULL',
            'NOT NULL': 'NOT NULL',
            FIND_IN_SET: 'FIND_IN_SET',
        },
    },
    sortable: {
        type: 'select',
        value: 'false',
        options: {
            false: i18n.global.t('Disable'),
            custom: i18n.global.t('Enable'),
        },
    },
}

const formBaseAttr = {
    validator: {
        type: 'selects',
        value: [],
        options: {
            required: i18n.global.t('validate.required'),
            mobile: i18n.global.t('utils.mobile'),
            account: i18n.global.t('utils.account'),
            password: i18n.global.t('utils.password'),
            varName: i18n.global.t('utils.variable name'),
            editorRequired: i18n.global.t('validate.editor required'),
            number: i18n.global.t('utils.number'),
            integer: i18n.global.t('utils.integer'),
            float: i18n.global.t('utils.float'),
            date: i18n.global.t('utils.date'),
            url: 'URL',
            email: i18n.global.t('utils.email'),
        },
    },
    validatorMsg: {
        type: 'textarea',
        value: '',
        placeholder: i18n.global.t('crud.state.If left blank, the verifier title attribute will be filled in automatically'),
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
        name: i18n.global.t('crud.state.Primary key'),
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
        name: i18n.global.t('crud.state.Weight (automatically generate drag sort button)'),
        table: {
            operator: getTableAttr('operator', 'RANGE'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: formBaseAttr,
    },
    timestamp: {
        name: i18n.global.t('crud.state.Time date (timestamp storage)'),
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
        name: i18n.global.t('utils.string'),
        table: {
            ...tableBaseAttr,
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: formBaseAttr,
    },
    password: {
        name: i18n.global.t('utils.password'),
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['password']),
        },
    },
    number: {
        name: i18n.global.t('utils.number'),
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
    float: {
        name: i18n.global.t('utils.float'),
        table: {
            ...tableBaseAttr,
            operator: getTableAttr('operator', 'RANGE'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['float']),
            step: {
                type: 'number',
                value: 1,
            },
        },
    },
    radio: {
        name: i18n.global.t('utils.radio'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tag'),
        },
        form: formBaseAttr,
    },
    checkbox: {
        name: i18n.global.t('utils.checkbox'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tags'),
        },
        form: formBaseAttr,
    },
    switch: {
        name: i18n.global.t('utils.switch'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'switch'),
        },
        form: formBaseAttr,
    },
    textarea: {
        name: i18n.global.t('utils.textarea'),
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
        name: i18n.global.t('utils.array'),
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: formBaseAttr,
    },
    datetime: {
        name: i18n.global.t('utils.time date') + i18n.global.t('utils.choice'),
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
        name: i18n.global.t('utils.year') + i18n.global.t('utils.choice'),
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
        name: i18n.global.t('utils.date') + i18n.global.t('utils.choice'),
        table: {
            operator: getTableAttr('operator', '='),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['date']),
        },
    },
    time: {
        name: i18n.global.t('utils.time') + i18n.global.t('utils.choice'),
        table: {
            operator: getTableAttr('operator', '='),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: formBaseAttr,
    },
    select: {
        name: i18n.global.t('utils.select'),
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
        name: i18n.global.t('utils.remote select') + i18n.global.t('utils.choice'),
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
                placeholder: i18n.global.t('crud.state.If it is not input, it will be automatically analyzed by the controller'),
            },
        },
    },
    editor: {
        name: i18n.global.t('utils.rich Text'),
        table: {
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            validator: getFormAttr('validator', ['editorRequired']),
        },
    },
    city: {
        name: i18n.global.t('utils.city select'),
        table: {
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: formBaseAttr,
    },
    image: {
        name: i18n.global.t('utils.image') + i18n.global.t('Upload'),
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
        name: i18n.global.t('utils.file') + i18n.global.t('Upload'),
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
        name: i18n.global.t('utils.icon select'),
        table: {
            render: getTableAttr('render', 'icon'),
            operator: getTableAttr('operator', 'false'),
        },
        form: formBaseAttr,
    },
}

export const tableFieldsKey = ['quickSearchField', 'formFields', 'columnFields']
