import { ElNotification } from 'element-plus'
import { Axios, getUrl } from '/@/utils/axios'

const entryFile = '/index.php'

export const envBaseCheckUrl = entryFile + '/api/install/envbasecheck'
export const envNpmCheckUrl = entryFile + '/api/install/envnpmcheck'
export const testDatabaseUrl = entryFile + '/api/install/testdatabase'
export const baseConfigUrl = entryFile + '/api/install/baseconfig'
export const commandExecCompleteUrl = entryFile + '/api/install/commandexeccomplete'
export const mvDistUrl = entryFile + '/api/install/mvDist'
export const manualInstallUrl = entryFile + '/api/install/manualInstall'
export const terminalUrl = entryFile + '/api/install/terminal'

/**
 * 安装环境检查
 */
export const getEnvBaseCheck = () => {
    return Axios.get(envBaseCheckUrl)
}

/**
 * npm环境检查
 */
export const getEnvNpmCheck = () => {
    return Axios.get(envNpmCheckUrl)
}

/**
 * 测试数据库连接
 */
export const postTestDatabase = (database: anyObj) => {
    return Axios.post(testDatabaseUrl, database)
}

/**
 * 站点基本配置
 */
export const getBaseConfig = () => {
    return Axios.get(baseConfigUrl)
}

/**
 * 保存站点基本配置
 */
export const postBaseConfig = (values: anyObj) => {
    return Axios.post(baseConfigUrl, values)
}

/**
 * 标记命令执行完毕
 */
export const commandExecComplete = () => {
    Axios.post(commandExecCompleteUrl).then((res) => {
        if (res.data.code != 1) {
            ElNotification({
                type: 'error',
                message: res.data.msg,
            })
        }
    })
}

/**
 * 手动移动构建好的文件
 */
export const postMvDist = () => {
    return Axios.post(mvDistUrl)
}

export const getManualInstall = () => {
    return Axios.get(manualInstallUrl)
}

/**
 * 构建Terminal的Url
 */
export const buildTerminalUrl = (commandKey: string, outputExtend: string) => {
    return getUrl() + terminalUrl + '?command=' + commandKey + '&extend=' + outputExtend
}
