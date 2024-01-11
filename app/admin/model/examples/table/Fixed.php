<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Fixed
 */
class Fixed extends Model
{
    // 表名
    protected $name = 'examples_table_fixed';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

}