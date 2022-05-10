/*
 * 格式化时间戳
 */
export const timeFormat = (dateTime: string | number | null = null, fmt = 'yyyy-mm-dd hh:MM:ss') => {
    if (dateTime == 'none') return '无'
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

/**
 * 生成全球唯一标识
 * @returns uuid
 */
const hexList: string[] = []
for (let i = 0; i <= 15; i++) {
    hexList[i] = i.toString(16)
}
export function uuid(): string {
    let uuid = ''
    for (let i = 1; i <= 36; i++) {
        if (i === 9 || i === 14 || i === 19 || i === 24) {
            uuid += '-'
        } else if (i === 15) {
            uuid += 4
        } else if (i === 20) {
            uuid += hexList[(Math.random() * 4) | 8]
        } else {
            uuid += hexList[(Math.random() * 16) | 0]
        }
    }
    return uuid
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
