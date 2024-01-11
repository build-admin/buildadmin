<?php

namespace app\admin\model\examples\table\form;

use think\Model;

/**
 * Edit
 */
class Edit extends Model
{
    // 表名
    protected $name = 'examples_table_form_edit';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

}