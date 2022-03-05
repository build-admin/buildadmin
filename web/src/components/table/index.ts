import { useI18n } from 'vue-i18n'

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
