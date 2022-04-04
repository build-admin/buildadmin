<?php

namespace app\admin\model;

use think\Model;

class SensitiveDataLog extends Model
{
    protected $name = 'security_sensitive_data_log';

    protected $autoWriteTimestamp = 'int';
    protected $createTime         = 'createtime';
    protected $updateTime         = false;
}