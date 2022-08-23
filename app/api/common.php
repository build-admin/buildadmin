<?php

if (!function_exists('get_account_verification_type')) {
    /**
     * 获取可用的账户验证方式
     * 用于：用户找回密码|用户注册
     * @return string[] email=电子邮件,mobile=手机短信验证
     */
    function get_account_verification_type(): array
    {
        $types = [];

        // 电子邮件，检查后台系统邮件配置是否全部填写
        $sysMailConfig = get_sys_config('', 'mail');
        $configured    = true;
        foreach ($sysMailConfig as $item) {
            if (!$item) {
                $configured = false;
            }
        }
        if ($configured) {
            $types[] = 'email';
        }

        return $types;
    }
}