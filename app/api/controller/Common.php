<?php

namespace app\api\controller;

use ba\Random;
use Throwable;
use ba\Captcha;
use think\Response;
use ba\ClickCaptcha;
use app\common\facade\Token;
use app\common\controller\Api;

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
    public function clickCaptcha()
    {
        $id      = $this->request->request('id/s');
        $captcha = new ClickCaptcha();
        $this->success('', $captcha->creat($id));
    }

    /**
     * 点选验证码检查
     * @throws Throwable
     */
    public function checkClickCaptcha()
    {
        $id      = $this->request->post('id/s');
        $info    = $this->request->post('info/s');
        $unset   = $this->request->post('unset/b', false);
        $captcha = new ClickCaptcha();
        if ($captcha->check($id, $info, $unset)) $this->success();
        $this->error();
    }

    public function refreshToken()
    {
        $refreshToken = $this->request->post('refresh_token');
        $refreshToken = Token::get($refreshToken, false);

        if (!$refreshToken || $refreshToken['expire_time'] < time()) {
            $this->error(__('Login expired, please login again.'));
        }

        $newToken = Random::uuid();

        if ($refreshToken['type'] == 'admin-refresh') {
            $baToken = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', ''));
            if (!$baToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baToken);
            Token::set($newToken, 'admin', $refreshToken['user_id'], 86400);
        } elseif ($refreshToken['type'] == 'user-refresh') {
            $baUserToken = $this->request->server('HTTP_BA_USER_TOKEN', $this->request->request('ba-user-token', ''));
            if (!$baUserToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baUserToken);
            Token::set($newToken, 'user', $refreshToken['user_id'], 86400);
        }

        $this->success('', [
            'type'  => $refreshToken['type'],
            'token' => $newToken
        ]);
    }
}