<?php

namespace app\admin\validate;

use think\Validate;

class SensitiveData extends Validate
{
    protected $failException = true;

    protected $rule = [
        'name'        => 'require',
        'controller'  => 'require|unique:security_sensitive_data',
        'data_table'  => 'require',
        'primary_key' => 'require',
        'data_fields' => 'require',
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
        'add'  => ['name', 'data_fields', 'controller', 'data_table', 'primary_key'],
        'edit' => ['name', 'data_fields', 'controller', 'data_table', 'primary_key'],
    ];

    public function __construct()
    {
        $this->field = [
            'name'        => __('Name'),
            'data_fields' => __('Data Fields'),
            'controller'  => __('Controller'),
            'data_table'  => __('Data Table'),
            'primary_key' => __('Primary Key'),
        ];
        parent::__construct();
    }
}