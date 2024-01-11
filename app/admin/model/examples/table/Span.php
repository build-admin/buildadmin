<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Span
 */
class Span extends Model
{
    // 表名
    protected $name = 'examples_table_span';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}