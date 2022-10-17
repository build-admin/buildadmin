<?php

namespace app\api\validate;

use think\Validate;

class Account extends Validate
{
    protected $failException = true;

    protected $rule = [
        'avatar'   => 'require',
        'nickname' => 'require|chsDash',
        'birthday' => 'date',
        'email'    => 'require|email|unique:user',
        'mobile'   => 'require|mobile|unique:user',
        'password' => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
        'account'  => 'require',
        'captcha'  => 'require',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'edit'             => ['avatar', 'nickname', 'birthday'],
        'changePassword'   => ['password'],
        'retrievePassword' => ['account', 'captcha', 'password'],
    ];

    public function __construct()
    {
        $this->field   = [
            'avatar'   => __('avatar'),
            'nickname' => __('nickname'),
            'birthday' => __('birthday'),
            'email'    => __('email'),
            'mobile'   => __('mobile'),
            'password' => __('password'),
        ];
        $this->message = array_merge($this->message, [
            'nickname.chsDash' => __('nicknameChsDash'),
            'password.regex'   => __('Please input correct password')
        ]);
        parent::__construct();
    }
}