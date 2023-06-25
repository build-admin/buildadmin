<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Config;

/**
 * 跨域请求支持
 * 安全起见，只支持了配置中的域名
 */
class AllowCrossDomain
{
    protected array $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'think-lang, server, ba-user-token, batoken, Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];

    /**
     * 跨域请求检测
     * @access public
     * @param Request    $request
     * @param Closure    $next
     * @param array|null $header
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?array $header = []): Response
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        $origin = $request->header('origin');
        if ($origin) {
            $info = parse_url($origin);

            // 获取跨域配置
            $corsDomain   = explode(',', Config::get('buildadmin.cors_request_domain'));
            $corsDomain[] = $request->host(true);

            if (in_array("*", $corsDomain) || in_array($origin, $corsDomain) || (isset($info['host']) && in_array($info['host'], $corsDomain))) {
                header("Access-Control-Allow-Origin: " . $origin);
            }
        }

        return $next($request)->header($header);
    }
}
