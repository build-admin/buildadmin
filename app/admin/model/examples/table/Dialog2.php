<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Dialog2
 */
class Dialog2 extends Model
{
    // 表名
    protected $name = 'examples_table_dialog2';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

}