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
 * 默认按钮点击事件处理
 */
export const defaultOptButtonsClick = (name: string, row: TableRow, field: TableColumn) => {
    console.log(name, row, field)
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
            click: defaultOptButtonsClick,
        },
        {
            name: 'delete',
            title: t('delete'),
            text: '',
            type: 'danger',
            icon: 'fa fa-trash',
            class: 'table-row-delete',
            click: defaultOptButtonsClick,
        },
    ]
}
