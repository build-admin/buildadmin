<?php

namespace app\api\model;

use think\model;

class UserScoreLog extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;
}