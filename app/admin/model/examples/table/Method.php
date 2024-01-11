<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Method
 */
class Method extends Model
{
    // 表名
    protected $name = 'examples_table_method';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

}