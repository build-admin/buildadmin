<?php

namespace app\common\model;

use think\model;

class UserMoneyLog extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function getMoneyAttr($value)
    {
        return bcdiv($value, 100, 2);
    }

    public function setMoneyAttr($value)
    {
        return bcmul($value, 100, 2);
    }

    public function getBeforeAttr($value)
    {
        return bcdiv($value, 100, 2);
    }

    public function setBeforeAttr($value)
    {
        return bcmul($value, 100, 2);
    }

    public function getAfterAttr($value)
    {
        return bcdiv($value, 100, 2);
    }

    public function setAfterAttr($value)
    {
        return bcmul($value, 100, 2);
    }
}