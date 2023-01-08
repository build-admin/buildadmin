interface UserMountedRet {
    type: 'jump' | 'break' | 'continue' | 'reload'
    [key: string]: any
}

export default function userMounted(): Promise<UserMountedRet> {
    // 通常用于会员中心初始化时接受各种回调或收参跳转，返回 true 将终止会员中心初始化
    return new Promise((resolve) => {
        resolve({ type: 'continue' })
    })
}
