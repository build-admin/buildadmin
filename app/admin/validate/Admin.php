<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $failException = true;

    protected $rule = [
        'username'  => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:admin',
        'nickname'  => 'require',
        'password'  => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
        'email'     => 'email|unique:admin',
        'mobile'    => 'mobile|unique:admin',
        'group_arr' => 'require|array',
    ];

    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['username', 'nickname', 'password', 'email', 'mobile', 'group_arr'],
    ];

    /**
     * 验证场景-前台自己修改自己资料
     */
    public function sceneInfo(): Admin
    {
        return $this->only(['nickname', 'password', 'email', 'mobile'])
            ->remove('password', 'require');
    }

    /**
     * 验证场景-编辑资料
     */
    public function sceneEdit(): Admin
    {
        return $this->only(['username', 'nickname', 'password', 'email', 'mobile', 'group_arr'])
            ->remove('password', 'require');
    }

    public function __construct()
    {
        $this->field   = [
            'username'  => __('Username'),
            'nickname'  => __('Nickname'),
            'password'  => __('Password'),
            'email'     => __('Email'),
            'mobile'    => __('Mobile'),
            'group_arr' => __('Group Name Arr'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password')
        ]);
        parent::__construct();
    }
}