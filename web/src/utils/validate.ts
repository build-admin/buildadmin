import type { RuleType } from 'async-validator'
import { FormItemRule } from 'element-plus'
import { i18n } from '../lang'

/**
 * 手机号码验证
 */
export function validatorMobile(rule: any, mobile: string | number, callback: Function) {
    // 允许空值，若需必填请添加多验证规则
    if (!mobile) {
        return callback()
    }
    if (!/^(1[3-9])\d{9}$/.test(mobile.toString())) {
        return callback(new Error(i18n.global.t('validate.Please enter the correct mobile number')))
    }
    return callback()
}

/**
 * 身份证号验证
 */
export function validatorIdNumber(rule: any, idNumber: string | number, callback: Function) {
    if (!idNumber) {
        return callback()
    }
    if (!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(idNumber.toString())) {
        return callback(new Error(i18n.global.t('validate.Please enter the correct ID number')))
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
        return callback(new Error(i18n.global.t('validate.Please enter the correct account')))
    }
    return callback()
}

/**
 * 密码验证
 */
export function regularPassword(val: string) {
    return /^(?!.*[&<>"'\n\r]).{6,32}$/.test(val)
}
export function validatorPassword(rule: any, val: string, callback: Function) {
    if (!val) {
        return callback()
    }
    if (!regularPassword(val)) {
        return callback(new Error(i18n.global.t('validate.Please enter the correct password')))
    }
    return callback()
}

/**
 * 变量名验证
 */
export function regularVarName(val: string) {
    return /^([^\x00-\xff]|[a-zA-Z_$])([^\x00-\xff]|[a-zA-Z0-9_$])*$/.test(val)
}
export function validatorVarName(rule: any, val: string, callback: Function) {
    if (!val) {
        return callback()
    }
    if (!regularVarName(val)) {
        return callback(new Error(i18n.global.t('validate.Please enter the correct name')))
    }
    return callback()
}

export function validatorEditorRequired(rule: any, val: string, callback: Function) {
    if (!val || val == '<p><br></p>') {
        return callback(new Error(i18n.global.t('validate.Content cannot be empty')))
    }
    return callback()
}

/**
 * 支持的表单验证规则
 */
export const validatorType = {
    required: i18n.global.t('validate.required'),
    mobile: i18n.global.t('utils.mobile'),
    idNumber: i18n.global.t('utils.Id number'),
    account: i18n.global.t('utils.account'),
    password: i18n.global.t('utils.password'),
    varName: i18n.global.t('utils.variable name'),
    editorRequired: i18n.global.t('validate.editor required'),
    url: 'URL',
    email: i18n.global.t('utils.email'),
    date: i18n.global.t('utils.date'),
    number: i18n.global.t('utils.number'),
    integer: i18n.global.t('utils.integer'),
    float: i18n.global.t('utils.float'),
}

export interface buildValidatorParams {
    // 规则名:required=必填,mobile=手机号,idNumber=身份证号,account=账户,password=密码,varName=变量名,editorRequired=富文本必填,number、integer、float、date、url、email
    name:
        | 'required'
        | 'mobile'
        | 'idNumber'
        | 'account'
        | 'password'
        | 'varName'
        | 'editorRequired'
        | 'number'
        | 'integer'
        | 'float'
        | 'date'
        | 'url'
        | 'email'
    // 自定义验证错误消息
    message?: string
    // 验证项的标题，这些验证方式不支持:mobile、account、password、varName、editorRequired
    title?: string
    // 验证触发方式
    trigger?: 'change' | 'blur'
}

/**
 * 构建表单验证规则
 * @param {buildValidatorParams} paramsObj 参数对象
 */
export function buildValidatorData({ name, message, title, trigger = 'blur' }: buildValidatorParams): FormItemRule {
    // 必填
    if (name == 'required') {
        return {
            required: true,
            message: message ? message : i18n.global.t('Please input field', { field: title }),
            trigger: trigger,
        }
    }

    // 常见类型
    const validatorType = ['number', 'integer', 'float', 'date', 'url', 'email']
    if (validatorType.includes(name)) {
        return {
            type: name as RuleType,
            message: message ? message : i18n.global.t('Please enter the correct field', { field: title }),
            trigger: trigger,
        }
    }

    // 自定义验证方法
    const validatorCustomFun: anyObj = {
        mobile: validatorMobile,
        idNumber: validatorIdNumber,
        account: validatorAccount,
        password: validatorPassword,
        varName: validatorVarName,
        editorRequired: validatorEditorRequired,
    }
    if (validatorCustomFun[name]) {
        return {
            required: name == 'editorRequired' ? true : false,
            validator: validatorCustomFun[name],
            trigger: trigger,
            message: message,
        }
    }
    return {}
}
