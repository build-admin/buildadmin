<?php

namespace app\admin\validate;

use think\Validate;

class UserScoreLog extends Validate
{
    protected $failException = true;

    protected $rule = [
        'user_id' => 'require',
        'score'   => 'require',
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
        'add'  => ['user_id', 'score', 'memo'],
        'edit' => ['user_id', 'score', 'memo'],
    ];

    public function __construct()
    {
        $this->field = [
            'user_id' => __('user_id'),
            'score'   => __('score'),
            'memo'    => __('memo'),
        ];
        parent::__construct();
    }
}