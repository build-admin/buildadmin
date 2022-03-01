<?php
declare (strict_types=1);

namespace app\admin\controller;

use ba\Captcha;
use think\facade\Config;
use think\facade\Validate;
use app\common\controller\Backend;

class Index extends Backend
{
    protected $noNeedLogin      = ['test', 'login'];
    protected $noNeedPermission = ['index'];

    public function index()
    {
        $adminInfo = $this->auth->getInfo();
        unset($adminInfo['token']);
        $this->success('ok', [
            'adminInfo' => $adminInfo,
            'menus'     => $this->auth->getMenus()
        ]);
    }

    public function test()
    {

    }

    public function login()
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success('您已经登录过了，无需重复登录~', [
                'routeName' => 'admin'
            ], 302);
        }

        $captchaSwitch = Config::get('buildadmin.admin_login_captcha');

        // 检查提交
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keep     = $this->request->post('keep');

            $rule = [
                'username|' . __('Username') => 'require|length:3,30',
                'password|' . __('Password') => 'require|length:3,30',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            if ($captchaSwitch) {
                $rule['captcha|' . __('Captcha')]     = 'require|length:4,6';
                $rule['captchaId|' . __('CaptchaId')] = 'require';

                $data['captcha']   = $this->request->post('captcha');
                $data['captchaId'] = $this->request->post('captcha_id');
            }
            $validate = Validate::rule($rule);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $captchaObj = new Captcha();
            if (!$captchaObj->check($data['captcha'], $data['captchaId'])) {
                $this->error(__('Please enter the correct verification code'));
            }

            // 记录登录LOG-待完善
            $res = $this->auth->login($username, $password, (bool)$keep);
            if ($res === true) {
                $this->success(__('登录成功！'), [
                    'userinfo'  => $this->auth->getInfo(),
                    'routeName' => 'admin'
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('用户名或密码不正确！');
                $this->error($msg);
            }
        }

        $this->success('ok', [
            'captcha' => $captchaSwitch
        ]);
    }
}
