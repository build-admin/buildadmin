<?php
declare (strict_types=1);

namespace app\admin\controller;

use bd\Random;
use app\common\controller\Backend;
use app\common\facade\Token;
use think\facade\Db;

class Index extends Backend
{
    public function index()
    {
        return '后台首页';
    }

    public function login()
    {
        $url   = $this->request->get('url', '/admin');
        $token = Random::uuid();
        // Db::name

        // 检查登录态

        // 检查提交
        if ($this->request->isPost()) {
            $username  = $this->request->post('username');
            $password  = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');

            print_r($username);
        }
    }
}
