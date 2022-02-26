<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 允许跨域访问的域名
    'cors_request_domain' => 'localhost,127.0.0.1',
    'admin_login_captcha' => true,
    // 管理员登录失败可重试次数,false则无限
    'admin_login_retry'   => 10,
    // 允许执行的命令
    'allowed_commands'    => [
        'ping-baidu'   => 'ping baidu.com',
        'cnpm-install' => 'cnpm install',
        'npm-v'        => 'npm -v',
        'cnpm-v'       => 'cnpm -v',
        'node-v'       => 'node -v',
        'install-cnpm' => 'npm install -g cnpm --registry=https://registry.npmmirror.com',
        'test-install' => 'cd npm-install-test && cnpm install',
        'web-install'  => 'cd ../web && cnpm install',
        'web-build'    => 'cd ../web && cnpm run build:online',
    ],
    'token'               => [
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
                'type'        => 'Redis',
                'host'        => '127.0.0.1',
                'port'        => 6379,
                'password'    => '',
                'select'      => 0,
                'timeout'     => 0,
                'expire'      => 0,
                'persistent'  => false,
                'userprefix'  => 'up:',
                'tokenprefix' => 'tp:',
            ]
        ]
    ]
];