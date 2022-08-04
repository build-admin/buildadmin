export const stringToArray = (val: string | string[]) => {
    if (typeof val === 'string') {
        return val == '' ? [] : val.split(',')
    } else {
        return val as string[]
    }
}
