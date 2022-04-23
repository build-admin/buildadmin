<?php

namespace app\admin\model;

use think\Model;

/**
 * SensitiveDataLog 模型
 * @controllerUrl 'securitySensitiveDataLog'
 */
class SensitiveDataLog extends Model
{
    protected $name = 'security_sensitive_data_log';

    protected $autoWriteTimestamp = 'int';
    protected $createTime         = 'createtime';
    protected $updateTime         = false;

    public function sensitive()
    {
        return $this->belongsTo(SensitiveData::class, 'sensitive_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}