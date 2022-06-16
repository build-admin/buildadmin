<?php

namespace app\admin\validate;

use think\Validate;

class Config extends Validate
{
    protected $failException = true;

    protected $rule = [
        'name' => 'require|unique:config',
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
        'add' => ['name'],
    ];

    public function __construct()
    {
        $this->field = [
            'name' => __('Variable name'),
        ];
        parent::__construct();
    }
}