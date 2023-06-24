<?php

namespace app\api\controller;

use Throwable;
use ba\Captcha;
use ba\ClickCaptcha;
use think\facade\Validate;
use app\common\model\User;
use app\common\library\Email;
use app\common\controller\Frontend;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Ems extends Frontend
{
    protected array $noNeedLogin = ['send'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 发送邮件
     * event 事件:user_register=用户注册,user_change_email=用户修改邮箱,user_retrieve_pwd=用户找回密码,user_email_verify=验证账户
     * 不同的事件，会自动做各种必要检查，其中 验证账户 要求用户输入当前密码才能发送验证码邮件
     * @throws Throwable
     */
    public function send()
    {
        $params = $this->request->post(['email', 'event', 'captchaId', 'captchaInfo']);
        $mail   = new Email();
        if (!$mail->configured) {
            $this->error(__('Mail sending service unavailable'));
        }

        $validate = Validate::rule([
            'email'       => 'require|email',
            'event'       => 'require',
            'captchaId'   => 'require',
            'captchaInfo' => 'require'
        ])->message([
            'email'       => 'email format error',
            'event'       => 'Parameter error',
            'captchaId'   => 'Captcha error',
            'captchaInfo' => 'Captcha error'
        ]);
        if (!$validate->check($params)) {
            $this->error(__($validate->getError()));
        }

        // 检查验证码
        $captchaObj   = new Captcha();
        $clickCaptcha = new ClickCaptcha();
        if (!$clickCaptcha->check($params['captchaId'], $params['captchaInfo'])) {
            $this->error(__('Captcha error'));
        }

        // 检查频繁发送
        $captcha = $captchaObj->getCaptchaData($params['email'] . $params['event']);
        if ($captcha && time() - $captcha['create_time'] < 60) {
            $this->error(__('Frequent email sending'));
        }

        // 检查邮箱
        $userInfo = User::where('email', $params['email'])->find();
        if ($params['event'] == 'user_register' && $userInfo) {
            $this->error(__('Email has been registered, please log in directly'));
        } elseif ($params['event'] == 'user_change_email' && $userInfo) {
            $this->error(__('The email has been occupied'));
        } elseif (in_array($params['event'], ['user_retrieve_pwd', 'user_email_verify']) && !$userInfo) {
            $this->error(__('Email not registered'));
        }

        // 通过邮箱验证账户
        if ($params['event'] == 'user_email_verify') {
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'));
            }
            if ($this->auth->email != $params['email']) {
                $this->error(__('Please use the account registration email to send the verification code'));
            }
            // 验证账户密码
            $password = $this->request->post('password');
            if ($this->auth->password != encrypt_password($password, $this->auth->salt)) {
                $this->error(__('Password error'));
            }
        }

        // 生成一个验证码
        $code    = $captchaObj->create($params['email'] . $params['event']);
        $subject = __($params['event']) . '-' . get_sys_config('site_name');
        $body    = __('Your verification code is: %s', [$code]);

        try {
            $mail->isSMTP();
            $mail->addAddress($params['email']);
            $mail->isHTML();
            $mail->setSubject($subject);
            $mail->Body = $body;
            $mail->send();
        } catch (PHPMailerException) {
            $this->error($mail->ErrorInfo);
        }

        $this->success(__('Mail sent successfully~'));
    }
}