<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 允许跨域访问的域名
    'cors_request_domain'  => 'localhost,127.0.0.1',
    // 是否开启管理员登录验证码
    'admin_login_captcha'  => true,
    // 管理员登录失败可重试次数,false则无限
    'admin_login_retry'    => 10,
    // 表格拖拽排序时,两个权重相等则自动重新整理;控制器类中也有此项（作为单控制器自定义配置）
    'auto_sort_eq_weight'  => false,
    // npm包管理器
    'npm_package_manager'  => 'pnpm',
    // 安装服务端口
    'install_service_port' => '8000',
    // 允许执行的命令
    'allowed_commands'     => [
        'install-package-manager' => [
            'cnpm' => 'npm install cnpm -g --registry=https://registry.npmmirror.com',
            'yarn' => 'npm install -g yarn',
            'pnpm' => 'npm install -g pnpm',
            'ni'   => 'npm install -g @antfu/ni',
        ],
        'version-view'            => [
            'npm'  => 'npm -v',
            'cnpm' => 'cnpm -v',
            'yarn' => 'yarn -v',
            'pnpm' => 'pnpm -v',
            'node' => 'node -v',
        ],
        'test-install'            => [
            'npm'  => 'cd npm-install-test && npm install',
            'cnpm' => 'cd npm-install-test && cnpm install',
            'yarn' => 'cd npm-install-test && yarn install',
            'pnpm' => 'cd npm-install-test && pnpm install',
            'ni'   => 'cd npm-install-test && ni install',
        ],
        'web-install'             => [
            'npm'  => 'cd ../web && npm install',
            'cnpm' => 'cd ../web && cnpm install',
            'yarn' => 'cd ../web && yarn install',
            'pnpm' => 'cd ../web && pnpm install',
            'ni'   => 'cd ../web && ni install',
        ],
        'web-build'               => [
            'npm'  => 'cd ../web && npm run build:online',
            'cnpm' => 'cd ../web && cnpm run build:online',
            'yarn' => 'cd ../web && yarn run build:online',
            'pnpm' => 'cd ../web && pnpm run build:online',
            'ni'   => 'cd ../web && nr build:online',
        ],
        'ping-baidu'              => 'ping baidu.com',
    ],
    'token'                => [
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
    ],
    // 自动写入管理员操作日志
    'auto_write_admin_log' => true,
    // 缺省头像图片路径
    'default_avatar'       => '/static/images/avatar.png',
    // 版本号
    'version'              => 'v1.0.1',
];