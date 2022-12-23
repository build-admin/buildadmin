export default function loginMounted(): Promise<boolean> {
    // 通常用于会员登录页初始化时接受各种回调或收参跳转，返回 true 将终止会员登录页初始化
    return new Promise((resolve) => resolve(false))
}
