<?php

namespace app\common\model;

use think\model;

class UserMoneyLog extends model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function getMoneyAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setMoneyAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    public function getBeforeAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setBeforeAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    public function getAfterAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setAfterAttr($value): string
    {
        return bcmul($value, 100, 2);
    }
}