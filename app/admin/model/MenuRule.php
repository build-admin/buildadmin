<?php

namespace app\admin\model;

use think\Model;

class MenuRule extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}