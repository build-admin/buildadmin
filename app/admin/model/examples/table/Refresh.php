<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Refresh
 */
class Refresh extends Model
{
    // 表名
    protected $name = 'examples_table_refresh';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

}