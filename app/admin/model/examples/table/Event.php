<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Event
 */
class Event extends Model
{
    // 表名
    protected $name = 'examples_table_event';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

}