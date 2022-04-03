<?php

namespace app\admin\validate;

use think\Validate;

class DataRecycle extends Validate
{
    protected $failException = true;

    protected $rule = [
        'name'        => 'require',
        'controller'  => 'require|unique:security_data_recycle',
        'data_table'  => 'require',
        'primary_key' => 'require',
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
        'add'  => ['name', 'controller', 'data_table', 'primary_key'],
        'edit' => ['name', 'controller', 'data_table', 'primary_key'],
    ];

    public function __construct()
    {
        $this->field = [
            'name'        => __('Name'),
            'controller'  => __('Controller'),
            'data_table'  => __('Data Table'),
            'primary_key' => __('Primary Key'),
        ];
        parent::__construct();
    }
}