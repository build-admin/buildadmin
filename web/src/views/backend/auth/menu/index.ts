import baTableClass from '/@/utils/baTable'
import type { baTableApi } from '/@/api/common'

export class menuTableClass extends baTableClass {
    constructor(api: baTableApi, table: BaTable, form: BaTableForm = {}) {
        super(api, table, form)
    }

    getIndex() {
        this.table.loading = true
        return this.api
            .index(this.table.filter)
            .then((res) => {
                this.table.data = res.data.list
                this.table.remark = res.data.remark
                this.table.loading = false
            })
            .catch(() => {
                this.table.loading = false
            })
    }

    requestEdit(id: string) {
        this.form.items = {}
        return this.api
            .edit({
                id: id,
            })
            .then((res) => {
                this.form.items = res.data.row
                if (this.form.items && !this.form.items.icon) this.form.items.icon = 'el-icon-Minus'
                this.form.items!['pidebak'] = this.form.items!.pid
            })
    }

    onSubmit = () => {
        this.form.submitLoading = true
        if (this.form.items?.pid == this.form.items?.pidebak) {
            delete this.form.items?.pid
        }
        this.api
            .postData(this.form.operate!, this.form.items!)
            .then((res) => {
                this.onTableHeaderAction('refresh', {})
                this.form.submitLoading = false
                this.form.operateIds?.shift()
                if (this.form.operateIds?.length! > 0) {
                    this.toggleForm('edit', this.form.operateIds)
                } else {
                    this.toggleForm()
                }
            })
            .catch((err) => {
                this.form.submitLoading = false
            })
    }
}
