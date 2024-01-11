<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * HeaderBtn
 */
class HeaderBtn extends Model
{
    // 表名
    protected $name = 'examples_table_header_btn';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

}