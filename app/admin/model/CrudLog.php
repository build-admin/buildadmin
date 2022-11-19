<?php

namespace app\admin\model;

use think\Model;

/**
 * Log
 */
class CrudLog extends Model
{
    // 表名
    protected $name = 'crud_log';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    protected $type = [
        'table'  => 'array',
        'fields' => 'array',
    ];

}