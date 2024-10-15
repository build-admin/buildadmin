<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 允许跨域访问的域名
    'cors_request_domain'   => 'localhost,127.0.0.1',
    // 是否开启会员登录验证码
    'user_login_captcha'    => true,
    // 是否开启管理员登录验证码
    'admin_login_captcha'   => true,
    // 会员登录失败可重试次数,false则无限
    'user_login_retry'      => 10,
    // 管理员登录失败可重试次数,false则无限
    'admin_login_retry'     => 10,
    // 开启管理员单处登录它处失效
    'admin_sso'             => false,
    // 开启会员单处登录它处失效
    'user_sso'              => false,
    // 会员登录态保持时间（非刷新token，3天）
    'user_token_keep_time'  => 60 * 60 * 24 * 3,
    // 管理员登录态保持时间（非刷新token，3天）
    'admin_token_keep_time' => 60 * 60 * 24 * 3,
    // 表格拖拽排序时,两个权重相等则自动重新整理;控制器类中也有此项（作为单控制器自定义配置）
    'auto_sort_eq_weight'   => false,
    // 开启前台会员中心
    'open_member_center'    => true,
    // 模块纯净安装（安装时移动模块文件而不是复制）
    'module_pure_install'   => true,
    // 点选验证码配置
    'click_captcha'         => [
        // 模式:text=文字,icon=图标(若只有icon则适用于国际化站点)
        'mode'           => ['text', 'icon'],
        // 长度
        'length'         => 2,
        // 混淆点长度
        'confuse_length' => 2,
    ],
    // Token 配置
    'token'                 => [
        // 默认驱动方式
        'default' => 'mysql',
        // 加密key
        'key'     => 'tcbDgmqLVzuAdNH39o0QnhOisvSCFZ7I',
        // 加密方式
        'algo'    => 'ripemd160',
        // 驱动
        'stores'  => [
            'mysql' => [
                'type'   => 'Mysql',
                // 留空表示使用默认的 Mysql 数据库，也可以填写其他数据库连接配置的`name`
                'name'   => '',
                // 存储token的表名
                'table'  => 'token',
                // 默认 token 有效时间
                'expire' => 2592000,
            ],
            'redis' => [
                'type'       => 'Redis',
                'host'       => '127.0.0.1',
                'port'       => 6379,
                'password'   => '',
                // Db索引，非 0 以避免数据被意外清理
                'select'     => 1,
                'timeout'    => 0,
                // 默认 token 有效时间
                'expire'     => 2592000,
                'persistent' => false,
                'prefix'     => 'tk:',
            ],
        ]
    ],
    // 自动写入管理员操作日志
    'auto_write_admin_log'  => true,
    // 缺省头像图片路径
    'default_avatar'        => '/static/images/avatar.png',
    // 内容分发网络URL，末尾不带`/`
    'cdn_url'               => '',
    // 版本号
    'version'               => 'v2.1.2',
    // 接口地址
    'api_url'               => 'https://buildadmin.com',
];