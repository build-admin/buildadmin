<?php

namespace app\admin\model;

use think\model;
use think\Exception;

/**
 * UserMoneyLog 模型
 * @controllerUrl 'userMoneyLog'
 */
class UserMoneyLog extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;

    public static function onBeforeInsert($model)
    {
        $user = User::where('id', $model->user_id)->find();
        if (!$user) {
            throw new Exception("The user can't find it");
        }
        if (!$model->memo) {
            throw new Exception("Change note cannot be blank");
        }
        $model->before = $user->money;
        $model->after  = $user->money + $model->money;
    }

    public static function onAfterInsert($model)
    {
        $user = User::where('id', $model->user_id)->find();
        if (!$user) {
            $model->delete();
            throw new Exception("The user can't find it");
        }
        $user->money += $model->money;
        $user->save();
    }

    public static function onBeforeDelete()
    {
        return false;
    }

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}