<?php

namespace app\api\controller;

use ba\Random;
use Throwable;
use ba\Captcha;
use think\Response;
use ba\ClickCaptcha;
use think\facade\Config;
use app\common\facade\Token;
use app\common\controller\Api;
use app\admin\library\Auth as AdminAuth;
use app\common\library\Auth as UserAuth;

class Common extends Api
{
    /**
     * 图形验证码
     * @throws Throwable
     */
    public function captcha(): Response
    {
        $captchaId = $this->request->request('id');
        $config    = array(
            'codeSet'  => '123456789',            // 验证码字符集合
            'fontSize' => 22,                     // 验证码字体大小(px)
            'useCurve' => false,                  // 是否画混淆曲线
            'useNoise' => true,                   // 是否添加杂点
            'length'   => 4,                      // 验证码位数
            'bg'       => array(255, 255, 255),   // 背景颜色
        );

        $captcha = new Captcha($config);
        return $captcha->entry($captchaId);
    }

    /**
     * 点选验证码
     */
    public function clickCaptcha(): void
    {
        $id      = $this->request->request('id/s');
        $captcha = new ClickCaptcha();
        $this->success('', $captcha->creat($id));
    }

    /**
     * 点选验证码检查
     * @throws Throwable
     */
    public function checkClickCaptcha(): void
    {
        $id      = $this->request->post('id/s');
        $info    = $this->request->post('info/s');
        $unset   = $this->request->post('unset/b', false);
        $captcha = new ClickCaptcha();
        if ($captcha->check($id, $info, $unset)) $this->success();
        $this->error();
    }

    public function refreshToken(): void
    {
        $refreshToken = $this->request->post('refreshToken');
        $refreshToken = Token::get($refreshToken);

        if (!$refreshToken || $refreshToken['expire_time'] < time()) {
            $this->error(__('Login expired, please login again.'));
        }

        $newToken = Random::uuid();

        // 管理员token刷新
        if ($refreshToken['type'] == AdminAuth::TOKEN_TYPE . '-refresh') {
            $baToken = get_auth_token();
            if (!$baToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baToken);
            Token::set($newToken, AdminAuth::TOKEN_TYPE, $refreshToken['user_id'], (int)Config::get('buildadmin.admin_token_keep_time'));
        }

        // 会员token刷新
        if ($refreshToken['type'] == UserAuth::TOKEN_TYPE . '-refresh') {
            $baUserToken = get_auth_token(['ba', 'user', 'token']);
            if (!$baUserToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baUserToken);
            Token::set($newToken, UserAuth::TOKEN_TYPE, $refreshToken['user_id'], (int)Config::get('buildadmin.user_token_keep_time'));
        }

        $this->success('', [
            'type'  => $refreshToken['type'],
            'token' => $newToken
        ]);
    }
}