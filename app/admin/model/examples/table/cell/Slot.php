<?php

namespace app\admin\model\examples\table\cell;

use think\Model;

/**
 * Slot
 */
class Slot extends Model
{
    // 表名
    protected $name = 'examples_table_cell_slot';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

}