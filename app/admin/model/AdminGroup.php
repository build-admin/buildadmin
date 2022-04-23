<?php

namespace app\admin\model;

use think\Model;

/**
 * AdminGroup模型
 * @controllerUrl 'authGroup'
 */
class AdminGroup extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}