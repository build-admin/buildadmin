<?php
// +----------------------------------------------------------------------
// | 节流设置
// +----------------------------------------------------------------------
use think\middleware\Throttle;
use think\middleware\throttle\CounterFixed;
use think\Request;
use think\Response;

return [
    // 缓存键前缀，防止键值与其他应用冲突
    'prefix'                       => 'throttle_',
    // 缓存的键，true 表示使用来源ip
    'key'                          => true,
    // 要被限制的请求类型, eg: GET POST PUT DELETE HEAD 等
    'visit_method'                 => ['GET', 'HEAD'],
    // 设置访问频率，例如 '10/m' 指的是允许每分钟请求10次;'10/60'指允许每60秒请求10次。值 null 表示不限制， eg: null 10/m  20/h  300/d 200/300
    'visit_rate'                   => '120/m',
    /*
     * 设置节流算法，组件提供了四种算法：
     *  - CounterFixed ：计数固定窗口
     *  - CounterSlider: 滑动窗口
     *  - TokenBucket : 令牌桶算法
     *  - LeakyBucket : 漏桶限流算法
     */
    'driver_name'                  => CounterFixed::class,
    // 响应体中设置速率限制的头部信息，含义见：https://docs.github.com/en/rest/overview/resources-in-the-rest-api#rate-limiting
    'visit_enable_show_rate_limit' => true,
    // 访问受限时返回的响应
    'visit_fail_response'          => function (Throttle $throttle, Request $request, int $wait_seconds) {
        return Response::create([
            'code' => 0,
            'msg'  => 'Please do not request frequently. Try again in ' . $wait_seconds . ' seconds.',
        ], 'json')->header([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 0,
            'Access-Control-Allow-Methods'     => '*',
            'Access-Control-Allow-Headers'     => '*',
            'Access-Control-Allow-Origin'      => '*',
        ]);
    },
];
