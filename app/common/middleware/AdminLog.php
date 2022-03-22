<?php

namespace app\common\middleware;

use think\facade\Config;
use app\admin\model\AdminLog as AdminLogModel;

class AdminLog
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        if ($request->isPost() && Config::get('buildadmin.auto_write_admin_log')) {
            AdminLogModel::record();
        }
        return $response;
    }
}