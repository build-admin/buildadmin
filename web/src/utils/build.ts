import { readdirSync, writeFile } from 'fs'
import { trimEnd } from 'lodash-es'

function getFileNames(dir: string) {
    const dirents = readdirSync(dir, {
        withFileTypes: true,
    })
    const fileNames: string[] = []
    for (const dirent of dirents) {
        if (!dirent.isDirectory()) fileNames.push(dirent.name.replace('.vue', ''))
    }
    return fileNames
}

/**
 * 生成 ./types/tableRenderer.d.ts 文件
 */
const buildTableRendererType = () => {
    let tableRenderer = getFileNames('./src/components/table/fieldRender/')

    // 增加 slot，去除 default
    tableRenderer.push('slot')
    tableRenderer = tableRenderer.filter((item) => item !== 'default')

    let tableRendererContent =
        '// 可用的表格单元格渲染器，本文件内容以 ./src/components/table/fieldRender/ 目录中的文件名自动生成\ntype tableRenderer =\n    | '
    for (const key in tableRenderer) {
        tableRendererContent += `'${tableRenderer[key]}'\n    | `
    }
    tableRendererContent = trimEnd(tableRendererContent, '    | ')

    writeFile('./types/tableRenderer.d.ts', tableRendererContent, 'utf-8', (err) => {
        if (err) throw err
    })
}

buildTableRendererType()
