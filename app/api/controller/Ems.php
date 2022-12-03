<?php

namespace app\api\controller;

use ba\Captcha;
use think\facade\Validate;
use app\common\model\User;
use app\common\library\Email;
use app\common\controller\Frontend;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Ems extends Frontend
{
    protected $noNeedLogin = ['send'];

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 发送邮件
     * event 事件:user_register=用户注册,user_change_email=用户修改邮箱,user_retrieve_pwd=用户找回密码,user_email_verify=验证账户
     * 不同的事件，会自动做各种必要检查，其中 验证账户 要求用户输入当前密码才能发送验证码邮件
     */
    public function send()
    {
        $email = $this->request->post('email');
        $event = $this->request->post('event');
        $mail  = new Email();
        if (!$mail->configured) {
            $this->error(__('Mail sending service unavailable'));
        }

        $validate = Validate::rule(['email' => 'require|email', 'event' => 'require'])->message(['email' => 'email format error', 'event' => 'Parameter error']);
        if (!$validate->check(['email' => $email, 'event' => $event])) {
            $this->error(__($validate->getError()));
        }

        // 检查频繁发送
        $captcha = (new Captcha())->getCaptchaData($email . $event);
        if ($captcha && time() - $captcha['createtime'] < 60) {
            $this->error(__('Frequent email sending'));
        }

        // 检查邮箱
        $userInfo = User::where('email', $email)->find();
        if ($event == 'user_register' && $userInfo) {
            $this->error(__('Email has been registered, please log in directly'));
        } elseif ($event == 'user_change_email' && $userInfo) {
            $this->error(__('The email has been occupied'));
        } elseif (in_array($event, ['user_retrieve_pwd', 'user_email_verify']) && !$userInfo) {
            $this->error(__('Email not registered'));
        }

        // 通过邮箱验证账户
        if ($event == 'user_email_verify') {
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'));
            }
            if ($this->auth->email != $email) {
                $this->error(__('Please use the account registration email to send the verification code'));
            }
            // 验证账户密码
            $password = $this->request->post('password');
            if ($this->auth->password != encrypt_password($password, $this->auth->salt)) {
                $this->error(__('Password error'));
            }
        }

        // 生成一个验证码
        $captcha = new Captcha();
        $code    = $captcha->create($email . $event);
        $subject = __($event) . '-' . get_sys_config('site_name');
        $body    = __('Your verification code is: %s', [$code]);

        try {
            $mail->isSMTP();
            $mail->addAddress($email);
            $mail->isHTML();
            $mail->setSubject($subject);
            $mail->Body = $body;
            $mail->send();
        } catch (PHPMailerException $e) {
            $this->error($mail->ErrorInfo);
        }

        $this->success(__('Mail sent successfully~'));
    }
}