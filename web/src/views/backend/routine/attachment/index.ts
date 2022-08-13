import { buildSuffixSvgUrl } from '/@/api/common'

/**
 * 表格和表单中文件预览图的生成
 */
export const previewRenderFormatter = (row: TableRow, column: TableColumn, cellValue: string) => {
    const imgSuffix = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']
    if (imgSuffix.includes(cellValue)) {
        return row.full_url
    }
    return buildSuffixSvgUrl(cellValue)
}
