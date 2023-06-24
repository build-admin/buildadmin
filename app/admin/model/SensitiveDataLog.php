<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * SensitiveDataLog 模型
 */
class SensitiveDataLog extends Model
{
    protected $name = 'security_sensitive_data_log';

    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function sensitive(): BelongsTo
    {
        return $this->belongsTo(SensitiveData::class, 'sensitive_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}