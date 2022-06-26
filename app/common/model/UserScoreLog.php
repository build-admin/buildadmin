<?php

namespace app\common\model;

use think\model;

class UserScoreLog extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;
}