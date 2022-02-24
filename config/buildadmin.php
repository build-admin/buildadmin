<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 允许跨域访问的域名
    'cors_request_domain' => 'localhost,127.0.0.1',
    'admin_login_captcha' => true,
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
];