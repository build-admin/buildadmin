<?php

namespace app\admin\model\examples\table\form;

use think\Model;

/**
 * Other
 */
class Other extends Model
{
    // 表名
    protected $name = 'examples_table_form_other';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}