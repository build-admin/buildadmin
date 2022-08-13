import { i18n } from '/@/lang/index'

/*
 * 默认Url点击事件处理
 */
export const openUrl = (url: string, field: TableColumn) => {
    if (field.target == '_blank') {
        window.open(url)
    } else {
        window.location.href = url
    }
}

/*
 * 默认按钮组
 */
export const defaultOptButtons = (optButType: DefaultOptButType[] = ['weigh-sort', 'edit', 'delete']): OptButton[] => {
    const optButtonsPre: Map<string, OptButton> = new Map([
        [
            'weigh-sort',
            {
                render: 'moveButton',
                name: 'weigh-sort',
                title: 'weigh-sort',
                text: '',
                type: 'info',
                icon: 'fa fa-arrows',
                class: 'table-row-weigh-sort',
                disabledTip: false,
            },
        ],
        [
            'edit',
            {
                render: 'tipButton',
                name: 'edit',
                title: 'edit',
                text: '',
                type: 'primary',
                icon: 'fa fa-pencil',
                class: 'table-row-edit',
                disabledTip: false,
            },
        ],
        [
            'delete',
            {
                render: 'confirmButton',
                name: 'delete',
                title: 'delete',
                text: '',
                type: 'danger',
                icon: 'fa fa-trash',
                class: 'table-row-delete',
                popconfirm: {
                    confirmButtonText: i18n.global.t('delete'),
                    cancelButtonText: i18n.global.t('Cancel'),
                    confirmButtonType: 'danger',
                    title: i18n.global.t('Are you sure to delete the selected record?'),
                },
                disabledTip: false,
            },
        ],
    ])

    const optButtons: OptButton[] = []
    for (const key in optButType) {
        if (optButtonsPre.has(optButType[key])) {
            optButtons.push(optButtonsPre.get(optButType[key])!)
        }
    }
    return optButtons
}

/*
 * 格式化时间戳
 */
export const timeFormat = (dateTime: string | number | null = null, fmt = 'yyyy-mm-dd hh:MM:ss') => {
    if (dateTime == 'none') return i18n.global.t('none')
    if (!dateTime) dateTime = Number(new Date())
    if (dateTime.toString().length === 10) {
        dateTime = +dateTime * 1000
    }

    const date = new Date(dateTime)
    let ret
    const opt: anyObj = {
        'y+': date.getFullYear().toString(), // 年
        'm+': (date.getMonth() + 1).toString(), // 月
        'd+': date.getDate().toString(), // 日
        'h+': date.getHours().toString(), // 时
        'M+': date.getMinutes().toString(), // 分
        's+': date.getSeconds().toString(), // 秒
    }
    for (const k in opt) {
        ret = new RegExp('(' + k + ')').exec(fmt)
        if (ret) {
            fmt = fmt.replace(ret[1], ret[1].length == 1 ? opt[k] : padStart(opt[k], ret[1].length, '0'))
        }
    }
    return fmt
}

/*
 * 字符串补位
 */
const padStart = (str: string, maxLength: number, fillString = ' ') => {
    if (str.length >= maxLength) return str

    const fillLength = maxLength - str.length
    let times = Math.ceil(fillLength / fillString.length)
    while ((times >>= 1)) {
        fillString += fillString
        if (times === 1) {
            fillString += fillString
        }
    }
    return fillString.slice(0, fillLength) + str
}

/**
 * 将带children的数组降维，然后寻找index所在的行
 */
export const findIndexRow = (data: TableRow[], findIdx: number, keyIndex: number | TableRow = -1): number | TableRow => {
    for (const key in data) {
        if (typeof keyIndex == 'number') {
            keyIndex++
        }

        if (keyIndex == findIdx) {
            return data[key]
        }

        if (data[key].children) {
            keyIndex = findIndexRow(data[key].children!, findIdx, keyIndex)
            if (typeof keyIndex != 'number') {
                return keyIndex
            }
        }
    }

    return keyIndex
}

type DefaultOptButType = 'weigh-sort' | 'edit' | 'delete'
