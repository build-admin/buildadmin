<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Expand
 */
class Expand extends Model
{
    // 表名
    protected $name = 'examples_table_expand';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}