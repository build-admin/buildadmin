/*
 * 语言包按需加载映射表
 * 使用固定字符串 ${lang} 指代当前语言
 * key为页面path，value为语言包文件相对路径，访问时，按需自动加载映射表的语言包，同时加载path对应的语言包（若存在）
 */
export default {
    '/': ['./frontend/${lang}/index.ts'],
    '/admin/moduleStore': ['./backend/${lang}/module.ts'],
    '/admin/user/rule': ['./backend/${lang}/auth/menu.ts'],
    '/admin/user/scoreLog': ['./backend/${lang}/user/moneyLog.ts'],
}
