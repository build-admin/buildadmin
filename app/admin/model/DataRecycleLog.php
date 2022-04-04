<?php

namespace app\admin\model;

use think\Model;

class DataRecycleLog extends Model
{
    protected $name = 'security_data_recycle_log';

    protected $autoWriteTimestamp = 'int';
    protected $createTime         = 'createtime';
    protected $updateTime         = false;

    public function recycle()
    {
        return $this->belongsTo(DataRecycle::class, 'recycle_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}