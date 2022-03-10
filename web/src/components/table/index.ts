import { useI18n } from 'vue-i18n'

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
export const defaultOptButtons = (): OptButton[] => {
    const { t } = useI18n()
    return [
        {
            name: 'edit',
            title: t('edit'),
            text: '',
            type: 'primary',
            icon: 'fa fa-pencil',
            class: 'table-row-edit',
        },
        {
            name: 'delete',
            title: t('delete'),
            text: '',
            type: 'danger',
            icon: 'fa fa-trash',
            class: 'table-row-delete',
            popconfirm: {
                confirmButtonText: '删除',
                cancelButtonText: '取消',
                confirmButtonType: 'danger',
                title: '确认要删除记录吗？',
            },
        },
    ]
}

/*
 * 格式化时间戳
 */
export const timeFormat = (dateTime: string | number | null = null, fmt = 'yyyy-mm-dd') => {
    if (!dateTime) dateTime = Number(new Date())
    if (dateTime.toString().length === 10) {
        dateTime = +dateTime * 1000
    }

    let date = new Date(dateTime)
    let ret
    let opt: anyObj = {
        'y+': date.getFullYear().toString(), // 年
        'm+': (date.getMonth() + 1).toString(), // 月
        'd+': date.getDate().toString(), // 日
        'h+': date.getHours().toString(), // 时
        'M+': date.getMinutes().toString(), // 分
        's+': date.getSeconds().toString(), // 秒
    }
    for (let k in opt) {
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
const padStart = (str: string, maxLength: number, fillString: string = ' ') => {
    if (str.length >= maxLength) return str

    let fillLength = maxLength - str.length,
        times = Math.ceil(fillLength / fillString.length)
    while ((times >>= 1)) {
        fillString += fillString
        if (times === 1) {
            fillString += fillString
        }
    }
    return fillString.slice(0, fillLength) + str
}
