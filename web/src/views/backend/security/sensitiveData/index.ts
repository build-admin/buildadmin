import baTableClass from '/@/utils/baTable'
import type { baTableApi } from '/@/api/common'
import { add } from '/@/api/backend/security/sensitiveData'
import { getFieldList } from '/@/api/backend/security/sensitiveData'
import { uuid } from '/@/utils/random'

export interface DataFields {
    name: string
    value: string
}

export class sensitiveDataClass extends baTableClass {
    constructor(api: baTableApi, table: BaTable, form: BaTableForm = {}, before: BaTableBefore = {}, after: BaTableAfter = {}) {
        super(api, table, form, before, after)
    }

    // 重写编辑
    requestEdit = (id: string) => {
        this.runBefore('requestEdit', { id })
        this.form.loading = true
        this.form.items = {}
        return this.api
            .edit({
                id: id,
            })
            .then((res) => {
                const fields: string[] = []
                const dataFields: DataFields[] = []
                for (const key in res.data.row.data_fields) {
                    fields.push(key)
                    dataFields.push({
                        name: key,
                        value: res.data.row.data_fields[key] ?? '',
                    })
                }

                this.form.extend = Object.assign(this.form.extend!, {
                    tableList: res.data.tables,
                    controllerList: res.data.controllers,
                })

                if (res.data.row.data_table) {
                    this.onTableChange(res.data.row.data_table)
                    if (this.form.extend!.parentRef) this.form.extend!.parentRef.setDataFields(dataFields)
                }

                res.data.row.data_fields = fields
                this.form.loading = false
                this.form.items = res.data.row
                this.runAfter('requestEdit', { res })
            })
    }

    // 数据表改变事件
    onTableChange = (table: string) => {
        this.form.extend = Object.assign(this.form.extend!, {
            fieldLoading: true,
            fieldlist: {},
            fieldSelect: {},
            fieldSelectKey: uuid(),
        })

        this.form.items!.data_fields = []
        if (this.form.extend!.parentRef) this.form.extend!.parentRef.setDataFields([])

        getFieldList(table).then((res) => {
            this.form.items!.primary_key = res.data.pk
            this.form.defaultItems!.primary_key = res.data.pk

            const fieldSelect: anyObj = {}
            for (const key in res.data.fieldlist) {
                fieldSelect[key] = (key ? key + ' - ' : '') + res.data.fieldlist[key]
            }

            this.form.extend = Object.assign(this.form.extend!, {
                fieldLoading: false,
                fieldlist: res.data.fieldlist,
                fieldSelect: fieldSelect,
                fieldSelectKey: uuid(),
            })
        })
    }

    /**
     * 重写打开表单方法
     */
    toggleForm = (operate = '', operateIds: string[] = []) => {
        this.runBefore('toggleForm', { operate, operateIds })
        if (this.form.ref) {
            this.form.ref.resetFields()
        }

        if (this.form.extend!.parentRef) this.form.extend!.parentRef.setDataFields([])

        if (operate == 'edit') {
            if (!operateIds.length) {
                return false
            }
            this.requestEdit(operateIds[0])
        } else if (operate == 'add') {
            this.form.loading = true
            add().then((res) => {
                this.form.extend = Object.assign(this.form.extend!, {
                    tableList: res.data.tables,
                    controllerList: res.data.controllers,
                })
                this.form.items = Object.assign({}, this.form.defaultItems)
                this.form.loading = false
            })
        }

        this.form.operate = operate
        this.form.operateIds = operateIds
        this.runAfter('toggleForm', { operate, operateIds })
    }
}
