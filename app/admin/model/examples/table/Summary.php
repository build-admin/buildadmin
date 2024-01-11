<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Summary
 */
class Summary extends Model
{
    // 表名
    protected $name = 'examples_table_summary';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    public function getFloat1Attr($value): float
    {
        return (float)$value;
    }

    public function getFloat2Attr($value): float
    {
        return (float)$value;
    }
}