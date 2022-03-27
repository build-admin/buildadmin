<?php

namespace app\admin\validate;

use think\Validate;

class UserMoneyLog extends Validate
{
    protected $failException = true;

    protected $rule = [
        'user_id' => 'require',
        'money'   => 'require',
        'memo'    => 'require',
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
        'add'  => ['user_id', 'money', 'memo'],
        'edit' => ['user_id', 'money', 'memo'],
    ];

    public function __construct()
    {
        $this->field = [
            'user_id' => __('user_id'),
            'money'   => __('money'),
            'memo'    => __('memo'),
        ];
        parent::__construct();
    }
}