/* vite相关 */
import dotenv from 'dotenv'

export interface ViteEnv {
    VITE_PORT: number
    VITE_OPEN: boolean
    VITE_BASE_PATH: string
    VITE_OUT_DIR: string
    VITE_AXIOS_BASE_URL: string
    VITE_PROXY_URL: string
}

export function isOnline(mode: string): boolean {
    return mode === 'online'
}

export function isDev(mode: string): boolean {
    return mode === 'development'
}

export function isProd(mode: string | undefined): boolean {
    return mode === 'production' || mode === 'online'
}

// Read all environment variable configuration files to process.env
export function loadEnv(mode: string): ViteEnv {
    const ret: any = {}
    const envList = [`.env.${mode}.local`, `.env.${mode}`, '.env.local', '.env']
    envList.forEach((e) => {
        dotenv.config({ path: e })
    })
    for (const envName of Object.keys(process.env)) {
        let realName = (process.env as any)[envName].replace(/\\n/g, '\n')
        realName = realName === 'true' ? true : realName === 'false' ? false : realName
        if (envName === 'VITE_PORT') realName = Number(realName)
        if (envName === 'VITE_OPEN') realName = Boolean(realName)
        if (envName === 'VITE_PROXY') {
            try {
                realName = JSON.parse(realName)
            } catch (error) {
                realName = ''
            }
        }
        ret[envName] = realName
        if (typeof realName === 'string') {
            process.env[envName] = realName
        } else if (typeof realName === 'object') {
            process.env[envName] = JSON.stringify(realName)
        }
    }
    return ret
}
