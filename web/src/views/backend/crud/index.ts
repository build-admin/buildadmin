import { reactive } from 'vue'
import { i18n } from '/@/lang/index'
import { validatorType } from '/@/utils/validate'
import { npuaFalse, fieldData } from '/@/components/baInput/helper'

/**
 * 字段修改类型标识
 * 改排序需要在表结构变更完成之后再单独处理所以标识独立
 */
export type TableDesignChangeType = 'change-field-name' | 'del-field' | 'add-field' | 'change-field-attr' | 'change-field-order'

export interface TableDesignChange {
    type: TableDesignChangeType
    // 字段在设计器中的数组 index
    index?: number
    // 字段旧名称（重命名、修改属性、删除）
    oldName: string
    // 字段新名称（重命名、添加）
    newName: string
    // 是否同步到数据表
    sync?: boolean
    // 此字段在 after 字段之后，值为`FIRST FIELD`表示它是第一个字段
    after?: string
}

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
    index?: number
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
            comment: 'ID',
            designType: 'pk',
            formBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.number,
            default: 'none',
            primaryKey: true,
            unsigned: true,
            autoIncrement: true,
        },
        {
            title: i18n.global.t('crud.state.Primary key (Snowflake ID)'),
            name: 'id',
            comment: 'ID',
            designType: 'spk',
            formBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.number,
            type: 'bigint',
            length: 20,
            default: 'none',
            primaryKey: true,
            unsigned: true,
        },
        {
            title: i18n.global.t('State'),
            name: 'status',
            comment: i18n.global.t('crud.state.Status:0=Disabled,1=Enabled'),
            designType: 'switch',
            table: {},
            form: {},
            ...fieldData.switch,
        },
        {
            title: i18n.global.t('crud.state.remarks'),
            name: 'remark',
            comment: i18n.global.t('crud.state.remarks'),
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.textarea,
        },
        {
            title: i18n.global.t('crud.state.Weight (drag and drop sorting)'),
            name: 'weigh',
            comment: i18n.global.t('Weigh'),
            designType: 'weigh',
            table: {},
            form: {},
            ...fieldData.number,
            default: '0',
            null: true,
        },
        {
            title: i18n.global.t('Update time'),
            name: 'update_time',
            comment: i18n.global.t('Update time'),
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.datetime,
        },
        {
            title: i18n.global.t('Create time'),
            name: 'create_time',
            comment: i18n.global.t('Create time'),
            designType: 'timestamp',
            formBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.datetime,
        },
        {
            title: i18n.global.t('crud.state.Remote Select (association table)'),
            name: 'remote_select',
            comment: i18n.global.t('utils.remote select'),
            designType: 'remoteSelect',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.remoteSelect,
        },
    ],
    base: [
        {
            title: i18n.global.t('utils.string'),
            name: 'string',
            comment: i18n.global.t('utils.string'),
            designType: 'string',
            table: {},
            form: {},
            ...fieldData.string,
        },
        {
            title: i18n.global.t('utils.image'),
            name: 'image',
            comment: i18n.global.t('utils.image'),
            designType: 'image',
            table: {},
            form: {},
            ...fieldData.image,
        },
        {
            title: i18n.global.t('utils.file'),
            name: 'file',
            comment: i18n.global.t('utils.file'),
            designType: 'file',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.file,
        },
        {
            title: i18n.global.t('utils.radio'),
            name: 'radio',
            dataType: "enum('opt0','opt1')",
            comment: i18n.global.t('crud.state.Radio:opt0=Option1,opt1=Option2'),
            designType: 'radio',
            table: {},
            form: {},
            ...fieldData.radio,
            default: 'opt0',
        },
        {
            title: i18n.global.t('utils.checkbox'),
            name: 'checkbox',
            dataType: "set('opt0','opt1')",
            comment: i18n.global.t('crud.state.Checkbox:opt0=Option1,opt1=Option2'),
            designType: 'checkbox',
            table: {},
            form: {},
            ...fieldData.checkbox,
            default: 'opt0,opt1',
        },
        {
            title: i18n.global.t('utils.select'),
            name: 'select',
            dataType: "enum('opt0','opt1')",
            comment: i18n.global.t('crud.state.Select:opt0=Option1,opt1=Option2'),
            designType: 'select',
            table: {},
            form: {},
            ...fieldData.select,
            default: 'opt0',
        },
        {
            title: i18n.global.t('utils.switch'),
            name: 'switch',
            comment: i18n.global.t('crud.state.Switch:0=off,1=on'),
            designType: 'switch',
            table: {},
            form: {},
            ...fieldData.switch,
        },
        {
            title: i18n.global.t('utils.rich Text'),
            name: 'editor',
            comment: i18n.global.t('utils.rich Text'),
            designType: 'editor',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.editor,
        },
        {
            title: i18n.global.t('utils.textarea'),
            name: 'textarea',
            comment: i18n.global.t('utils.textarea'),
            designType: 'textarea',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.textarea,
        },
        {
            title: i18n.global.t('utils.number'),
            name: 'number',
            comment: i18n.global.t('utils.number'),
            designType: 'number',
            table: {},
            form: {},
            ...fieldData.number,
        },
        {
            title: i18n.global.t('utils.float'),
            name: 'float',
            type: 'decimal',
            length: 5,
            precision: 2,
            default: '0',
            ...npuaFalse(),
            comment: i18n.global.t('utils.float'),
            designType: 'float',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.password'),
            name: 'password',
            comment: i18n.global.t('utils.password'),
            designType: 'password',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.password,
        },
        {
            title: i18n.global.t('utils.date'),
            name: 'date',
            comment: i18n.global.t('utils.date'),
            designType: 'date',
            table: {},
            form: {},
            ...fieldData.date,
        },
        {
            title: i18n.global.t('utils.time'),
            name: 'time',
            comment: i18n.global.t('utils.time'),
            designType: 'time',
            table: {},
            form: {},
            ...fieldData.time,
        },
        {
            title: i18n.global.t('utils.time date'),
            name: 'datetime',
            type: 'datetime',
            length: 0,
            precision: 0,
            default: 'null',
            ...npuaFalse(),
            null: true,
            comment: i18n.global.t('utils.time date'),
            designType: 'datetime',
            table: {},
            form: {},
        },
        {
            title: i18n.global.t('utils.year'),
            name: 'year',
            comment: i18n.global.t('utils.year'),
            designType: 'year',
            table: {},
            form: {},
            ...fieldData.year,
        },
        {
            title: i18n.global.t('crud.state.Time date (timestamp storage)'),
            name: 'timestamp',
            comment: i18n.global.t('utils.time date'),
            designType: 'timestamp',
            table: {},
            form: {},
            ...fieldData.datetime,
        },
    ],
    senior: [
        {
            title: i18n.global.t('utils.array'),
            name: 'array',
            comment: i18n.global.t('utils.array'),
            designType: 'array',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.array,
        },
        {
            title: i18n.global.t('utils.city select'),
            name: 'city',
            comment: i18n.global.t('utils.city select'),
            designType: 'city',
            table: {},
            form: {},
            ...fieldData.city,
        },
        {
            title: i18n.global.t('utils.icon select'),
            name: 'icon',
            comment: i18n.global.t('utils.icon select'),
            designType: 'icon',
            table: {},
            form: {},
            ...fieldData.icon,
        },
        {
            title: i18n.global.t('utils.color picker'),
            name: 'color',
            comment: i18n.global.t('utils.color picker'),
            designType: 'color',
            table: {},
            form: {},
            ...fieldData.color,
        },
        {
            title: i18n.global.t('utils.image') + i18n.global.t('crud.state.Multi'),
            name: 'images',
            comment: i18n.global.t('utils.image'),
            designType: 'images',
            table: {},
            form: {},
            ...fieldData.images,
        },
        {
            title: i18n.global.t('utils.file') + i18n.global.t('crud.state.Multi'),
            name: 'files',
            comment: i18n.global.t('utils.file'),
            designType: 'files',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.files,
        },
        {
            title: i18n.global.t('utils.select') + i18n.global.t('crud.state.Multi'),
            name: 'selects',
            comment: i18n.global.t('crud.state.Select:opt0=Option1,opt1=Option2'),
            designType: 'selects',
            table: {},
            form: {},
            ...fieldData.selects,
        },
        {
            title: i18n.global.t('crud.state.Remote Select (Multi)'),
            name: 'remote_select',
            comment: i18n.global.t('utils.remote select'),
            designType: 'remoteSelects',
            tableBuildExclude: true,
            table: {},
            form: {},
            ...fieldData.remoteSelects,
        },
    ],
}

const tableBaseAttr = {
    render: {
        type: 'select',
        value: 'none',
        options: {
            none: i18n.global.t('None'),
            icon: 'Icon',
            switch: i18n.global.t('utils.switch'),
            image: i18n.global.t('utils.image'),
            images: i18n.global.t('utils.multi image'),
            tag: 'Tag',
            tags: 'Tags',
            url: 'URL',
            datetime: i18n.global.t('utils.time date'),
            color: i18n.global.t('utils.color'),
        },
    },
    operator: {
        type: 'select',
        value: 'eq',
        options: {
            false: i18n.global.t('crud.state.Disable Search'),
            eq: 'eq =',
            ne: 'ne !=',
            gt: 'gt >',
            egt: 'egt >=',
            lt: 'lt <',
            elt: 'elt <=',
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
        options: validatorType,
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

export const getTableAttr = (type: keyof typeof tableBaseAttr, val: string) => {
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
    spk: {
        name: i18n.global.t('crud.state.Primary key (Snowflake ID)'),
        table: {
            width: {
                type: 'number',
                value: 180,
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
            operator: getTableAttr('operator', 'FIND_IN_SET'),
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
            operator: getTableAttr('operator', 'eq'),
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
            operator: getTableAttr('operator', 'eq'),
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
            operator: getTableAttr('operator', 'eq'),
            sortable: getTableAttr('sortable', 'custom'),
        },
        form: formBaseAttr,
    },
    select: {
        name: i18n.global.t('utils.select'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tag'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: false,
            },
        },
    },
    selects: {
        name: i18n.global.t('utils.select') + i18n.global.t('crud.state.Multi'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tags'),
            operator: getTableAttr('operator', 'FIND_IN_SET'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: true,
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
    remoteSelects: {
        name: i18n.global.t('utils.remote select') + i18n.global.t('utils.choice') + i18n.global.t('crud.state.Multi'),
        table: {
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: true,
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
            operator: getTableAttr('operator', 'false'),
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
    images: {
        name: i18n.global.t('utils.image') + i18n.global.t('Upload') + i18n.global.t('crud.state.Multi'),
        table: {
            render: getTableAttr('render', 'images'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'image-multi': {
                type: 'switch',
                value: true,
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
    files: {
        name: i18n.global.t('utils.file') + i18n.global.t('Upload') + i18n.global.t('crud.state.Multi'),
        table: {
            render: getTableAttr('render', 'none'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'file-multi': {
                type: 'switch',
                value: true,
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
    color: {
        name: i18n.global.t('utils.color picker'),
        table: {
            render: getTableAttr('render', 'color'),
            operator: getTableAttr('operator', 'false'),
        },
        form: formBaseAttr,
    },
}

export const tableFieldsKey = ['quickSearchField', 'formFields', 'columnFields']
