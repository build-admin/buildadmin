import type { RuleType } from 'async-validator'
import { FormItemRule } from 'element-plus/es/components/form/src/form.type'

/**
 * 手机号码验证
 * 用于表单验证
 */
export function validatorMobile(rule: any, mobile: string | number, callback: Function) {
    // 允许空值，若需必填请添加多验证规则
    if (!mobile) {
        return callback()
    }
    if (!/^((12[0-9])|(13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\d{8}$/.test(mobile.toString())) {
        return callback(new Error('请输入正确的手机号！'))
    }
    return callback()
}

/**
 * 账户名验证
 */
export function validatorAccount(rule: any, val: string, callback: Function) {
    if (!val) {
        return callback()
    }
    if (!/^[a-zA-Z][a-zA-Z0-9_]{2,15}$/.test(val)) {
        return callback(new Error('请输入正确的账户'))
    }
    return callback()
}

/**
 * 密码验证
 */
export function regularPassword(val: string) {
    if (/^[a-zA-Z0-9_]{6,32}$/.test(val)) return true
    return false
}
export function validatorPassword(rule: any, val: string, callback: Function) {
    if (!val) {
        return callback()
    }
    if (!regularPassword(val)) {
        return callback(new Error('请输入正确的密码'))
    }
    return callback()
}

/**
 * 变量名验证
 */
export function validatorVarName(rule: any, val: string, callback: Function) {
    if (!val) {
        return callback()
    }
    if (!/^([^\x00-\xff]|[a-zA-Z_$])([^\x00-\xff]|[a-zA-Z0-9_$])*$/.test(val)) {
        return callback(new Error('请输入正确的名称'))
    }
    return callback()
}

/**
 * 构建表单验证规则
 * @var ruleName 规则名:required=必填,mobile=手机号,account=账户,password=密码,varName=变量名,number、integer、float、date、url、email
 * @var title 验证项的标题
 * @var trigger 自定义验证触发方式
 * @var message 自定义验证错误消息
 */
export const validatorType = {
    required: '必填',
    mobile: '手机号',
    account: '账户名',
    password: '密码',
    varName: '变量名',
    url: 'URL',
    email: '邮箱地址',
    date: '日期',
    number: '数字',
    integer: '整数',
    float: '浮点数',
}
export function buildValidatorData(ruleName: string, title: string, trigger: string = 'blur', message: string = ''): FormItemRule {
    // 必填
    if (ruleName == 'required') {
        return {
            required: true,
            message: message ? message : '请输入' + title,
            trigger: trigger,
        }
    }

    // 常见类型
    let validatorType = ['number', 'integer', 'float', 'date', 'url', 'email']
    if (validatorType.includes(ruleName)) {
        return {
            type: ruleName as RuleType,
            message: message ? message : '请输入正确的' + title,
            trigger: trigger,
        }
    }

    // 自定义验证方法
    let validatorCustomFun: anyObj = {
        mobile: validatorMobile,
        account: validatorAccount,
        password: validatorPassword,
        varName: validatorVarName,
    }
    if (validatorCustomFun[ruleName]) {
        return {
            validator: validatorCustomFun[ruleName],
            trigger: trigger,
        }
    }
    return {}
}
