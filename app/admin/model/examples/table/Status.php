<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Status
 */
class Status extends Model
{
    // 表名
    protected $name = 'examples_table_status';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getFloatAttr($value): float
    {
        return (float)$value;
    }
}