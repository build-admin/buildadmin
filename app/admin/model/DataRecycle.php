<?php

namespace app\admin\model;

use think\Model;

class DataRecycle extends Model
{
    protected $name = 'security_data_recycle';

    protected $autoWriteTimestamp = 'int';
    protected $createTime         = 'createtime';
    protected $updateTime         = 'updatetime';
}