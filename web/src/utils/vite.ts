/**
 * 是否在开发环境
 */
export function isDev(mode: string): boolean {
    return mode === 'development'
}

/**
 * 是否在生产环境
 */
export function isProd(mode: string | undefined): boolean {
    return mode === 'production'
}
