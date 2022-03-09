<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Db;

class Dashboard extends Backend
{
    public function dashboard()
    {
        $this->success('', [
            'adminName' => $this->auth->nickname,
            'remark'    => get_route_remark()
        ]);
    }
}