<?php

namespace app\admin\model;

use think\model;
use app\admin\model\User;

class UserMoneyLog extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}