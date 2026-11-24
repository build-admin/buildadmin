import { chmodSync, existsSync, readdirSync, rmSync, writeFile, writeFileSync } from 'fs'
import { debounce, trimEnd } from 'lodash-es'
import { pathToFileURL } from 'node:url'
import { isAbsolute, join, relative, resolve } from 'path'
import type { Plugin } from 'vite'
import { createServer } from 'vite'
import { handleMsglist, mergeMsg } from '../lang/merge'

function formatTime(): string {
    const now = new Date()
    return now.toTimeString().slice(0, 8)
}

function gray(text: string): string {
    return `\x1b[90m${text}\x1b[0m`
}

function cyan(text: string): string {
    return `\x1b[36m${text}\x1b[0m`
}

// ========================== 生成 tableRenderer.d.ts ===============================

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
        '/** 可用的表格单元格渲染器，以 ./src/components/table/fieldRender/ 目录中的文件名自动生成 */\ntype TableRenderer =\n    | '
    for (const key in tableRenderer) {
        tableRendererContent += `'${tableRenderer[key]}'\n    | `
    }
    tableRendererContent = trimEnd(tableRendererContent, '    | ')

    writeFile('./types/tableRenderer.d.ts', tableRendererContent, 'utf-8', (err) => {
        if (err) throw err
    })

    console.log(`${gray(formatTime())} ${cyan('[table]')} updated: types/tableRenderer.d.ts`)
}

// ========================== 为开发环境生成 i18n Ally locale index ===============================

const LANG_DIR = './src/lang'

/**
 * 检测支持的语言，以 src/lang/globs-{locale}.ts 为标记
 */
function detectLocales(): string[] {
    const files = readdirSync(LANG_DIR)
    return files.filter((file) => file.startsWith('globs-') && file.endsWith('.ts')).map((file) => file.slice('globs-'.length, -'.ts'.length))
}

/**
 * 递归收集目录下所有 .ts 文件
 */
function collectTsFiles(dir: string): string[] {
    if (!existsSync(dir)) return []
    const files: string[] = []
    const entries = readdirSync(dir, { withFileTypes: true })
    for (const entry of entries) {
        const fullPath = join(dir, entry.name)
        if (entry.isDirectory()) {
            files.push(...collectTsFiles(fullPath))
        } else if (entry.name.endsWith('.ts')) {
            files.push(fullPath)
        }
    }
    return files
}

/**
 * 将单个语言包模块合并到总消息对象中
 * @param messages 总消息对象
 * @param moduleDefault 语言包模块的 default 导出
 * @param pathName 文件命名空间，空字符串表示合并到根层
 */
function mergeModule(messages: anyObj, moduleDefault: anyObj, pathName = '') {
    if (!moduleDefault || Object.keys(moduleDefault).length === 0) return
    if (!pathName) {
        mergeMsg(messages, moduleDefault)
        return
    }
    const msg: anyObj = {}
    if (pathName.indexOf('/') > 0) {
        handleMsglist(msg, moduleDefault, pathName)
    } else {
        msg[pathName] = moduleDefault
    }
    mergeMsg(messages, msg)
}

/**
 * 根据文件在语言目录中的相对路径推导命名空间
 * 例如 src/lang/backend/zh-cn/auth/admin.ts 基于 src/lang/backend/zh-cn 得到 auth/admin
 */
function getNestedPathName(filePath: string, baseDir: string): string {
    const rel = filePath.slice(baseDir.length + 1).replaceAll('\\', '/')
    return rel.slice(0, rel.lastIndexOf('.'))
}

let loadTsSeq = 0
async function loadTs(filePath: string): Promise<{ default: anyObj }> {
    const href = pathToFileURL(join(process.cwd(), filePath)).href
    return import(`${href}?t=${++loadTsSeq}`)
}

async function loadLocale(locale: string): Promise<anyObj> {
    const messages: anyObj = {}

    // 1. 全局公共语言包（平铺根层）
    const globs = await loadTs(join(LANG_DIR, `globs-${locale}.ts`))
    mergeModule(messages, globs.default)

    // 2. common 目录语言包，文件名/路径作为命名空间
    const commonBase = join(LANG_DIR, 'common', locale)
    for (const file of collectTsFiles(commonBase)) {
        const mod = await loadTs(file)
        const pathName = getNestedPathName(file, commonBase)
        mergeModule(messages, mod.default, pathName)
    }

    // 3. backend 公共语言包（根层）
    const backendRoot = join(LANG_DIR, 'backend', `${locale}.ts`)
    if (existsSync(backendRoot)) {
        const mod = await loadTs(backendRoot)
        mergeModule(messages, mod.default)
    }

    // 4. backend 页面语言包，文件路径作为命名空间
    const backendBase = join(LANG_DIR, 'backend', locale)
    for (const file of collectTsFiles(backendBase)) {
        const mod = await loadTs(file)
        const pathName = getNestedPathName(file, backendBase)
        mergeModule(messages, mod.default, pathName)
    }

    // 5. frontend 公共语言包（根层）
    const frontendRoot = join(LANG_DIR, 'frontend', `${locale}.ts`)
    if (existsSync(frontendRoot)) {
        const mod = await loadTs(frontendRoot)
        mergeModule(messages, mod.default)
    }

    // 6. frontend 页面语言包，文件路径作为命名空间
    const frontendBase = join(LANG_DIR, 'frontend', locale)
    for (const file of collectTsFiles(frontendBase)) {
        const mod = await loadTs(file)
        const pathName = getNestedPathName(file, frontendBase)
        mergeModule(messages, mod.default, pathName)
    }

    return messages
}

async function buildI18nAllyLocaleIndex() {
    const locales = detectLocales()

    // 清理旧的生成文件，避免语言被删除后残留
    for (const file of readdirSync(LANG_DIR)) {
        if (file.endsWith('.json')) {
            const filePath = join(LANG_DIR, file)
            // 旧文件可能是只读的，先解除只读再删除，确保可重复生成
            try {
                chmodSync(filePath, 0o666)
            } catch {}
            rmSync(filePath, { force: true })
        }
    }

    for (const locale of locales) {
        const messages = await loadLocale(locale)
        const filePath = join(LANG_DIR, `${locale}.json`)
        writeFileSync(filePath, JSON.stringify(messages, null, 4) + '\n', 'utf-8')
        // 设为只读，防止误编辑
        chmodSync(filePath, 0o444)
    }

    console.log(`${gray(formatTime())} ${cyan('[i18n-ally]')} updated: ${locales.map((locale) => `src/lang/${locale}.json`).join(', ')}`)
}

// ========================== 监听语言包目录变化自动重建 i18n Ally 索引 ===============================

const LANG_WATCH_DEBOUNCE_MS = 200

let langRebuildRunning = false
let langRebuildPending = false

/**
 * 重建 i18n Ally 索引（串行化：构建期间再次触发会排队，构建完成后再次构建）
 */
async function rebuildI18nAllyLocaleIndex() {
    if (langRebuildRunning) {
        langRebuildPending = true
        return
    }
    langRebuildRunning = true
    try {
        await buildI18nAllyLocaleIndex()
    } finally {
        langRebuildRunning = false
        if (langRebuildPending) {
            langRebuildPending = false
            scheduleI18nAllyRebuild()
        }
    }
}

/**
 * 防抖调度一次重建
 * 连续保存/批量改动合并为一次，变化停止 200ms 后执行；maxWait 保证持续变化时也至少每 1s 执行一次
 */
const scheduleI18nAllyRebuild = debounce(
    () => {
        rebuildI18nAllyLocaleIndex()
    },
    LANG_WATCH_DEBOUNCE_MS,
    {
        maxWait: LANG_WATCH_DEBOUNCE_MS * 5,
    }
)

/**
 * 监听 src/lang 目录语言包文件变化，自动重新生成 i18n Ally 索引的 Vite 插件
 */
function i18nAllyLocaleIndexPlugin(): Plugin {
    return {
        name: 'build-admin:i18n-ally-locale-index',
        apply: 'serve',
        configureServer(server) {
            if (!server.watcher) return
            const langDir = resolve(process.cwd(), LANG_DIR)

            // 是否为 src/lang 目录下的语言包源文件（.ts）
            const isLangTsFile = (filePath: string) => {
                if (!filePath.endsWith('.ts')) return false
                const rel = relative(langDir, filePath)
                return !rel.startsWith('..') && !isAbsolute(rel)
            }

            // 忽略 watcher 初始化扫描阶段的事件，避免启动时误触发一次重建
            let watcherReady = false
            server.watcher.on('ready', () => {
                watcherReady = true
            })

            const onLangChange = (filePath: string) => {
                if (!watcherReady || !isLangTsFile(filePath)) return
                scheduleI18nAllyRebuild()
            }
            server.watcher.on('add', onLangChange)
            server.watcher.on('change', onLangChange)
            server.watcher.on('unlink', onLangChange)
        },
    }
}

// ========================== 启动 Vite 开发服务器 ===============================

async function start() {
    // 1. 生成 tableRenderer.d.ts
    buildTableRendererType()

    // 2. 生成 i18n Ally 开发环境语言包索引
    await buildI18nAllyLocaleIndex()

    // 3. 使用 Vite 的 JS API 启动开发服务器
    const server = await createServer({
        // 等价于原命令中的 --force，强制重新预构建依赖
        optimizeDeps: { force: true },
        // 监听 src/lang 语言包变化，自动重新生成 i18n Ally 索引
        plugins: [i18nAllyLocaleIndexPlugin()],
    })
    await server.listen()
    server.printUrls()

    const shutdown = async () => {
        await server.close()
        process.exit(0)
    }
    process.on('SIGINT', shutdown)
}

start()
