<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

$server = isset($_REQUEST['server']) || isset($_SERVER['HTTP_SERVER']) || substr($_SERVER['REQUEST_URI'], 1, 9) == 'index.php' || $_SERVER['REQUEST_METHOD'] == 'OPTIONS';
if (!$server) {
    /*
     * 用户访问前端
     * 不在tp加载后判断，为了安全的使用 exit()（常驻内存运行时不走本文件）
     */
    $rootPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;

    // 安装检测-s
    if (!is_file($rootPath . 'install.lock') && is_file($rootPath . 'install' . DIRECTORY_SEPARATOR . 'index.html')) {
        header("location:" . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR);
        exit();
    }
    // 安装检测-e

    // 检测是否已编译前端（如果存在 index.html，则访问）-s
    if (is_file($rootPath . 'index.html')) {
        header("location:" . DIRECTORY_SEPARATOR . 'index.html');
        exit();
    }
    // 检测是否已编译前端-e
}

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
