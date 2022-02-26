<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Config;
use think\facade\Validate;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];

    public function index()
    {
        return '后台首页';
    }

    public function login()
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success('您已经登录过了~', [], 302);
        }

        $captcha = Config::get('buildadmin.admin_login_captcha');

        // 检查提交
        if ($this->request->isPost()) {
            $username  = $this->request->post('username');
            $password  = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');

            $rule = [
                'username|' . __('Username') => 'require|length:3,30',
                'password|' . __('Password') => 'require|length:3,30',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            if ($captcha) {
                $rule['captcha|' . __('Captcha')] = 'require|captcha';
                $data['captcha']                  = $this->request->post('captcha');
            }
            $validate = Validate::rule($rule);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            // 记录登录LOG-待完善
            $res = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($res === true) {
                $this->success(__('登录成功！'), [
                    'userinfo' => $this->auth->getInfo()
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('用户名或密码不正确！');
                $this->error($msg);
            }
        }

        $this->success('ok', [
            'captcha' => $captcha
        ]);
    }
}
