<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\controller\Backend;

class Index extends Backend
{
    public function index()
    {
        return '后台首页';
    }

    public function login()
    {
        $url = $this->request->get('url', '/admin');

        // 检查登录态

        // 检查提交
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');

            print_r($username);
        }
    }
}
