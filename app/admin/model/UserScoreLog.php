<?php

namespace app\admin\model;

use think\model;
use think\Exception;

/**
 * UserScoreLog 模型
 * @controllerUrl 'userScoreLog'
 */
class UserScoreLog extends model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public static function onBeforeInsert($model)
    {
        $user = User::where('id', $model->user_id)->find();
        if (!$user) {
            throw new Exception("The user can't find it");
        }
        if (!$model->memo) {
            throw new Exception("Change note cannot be blank");
        }
        $model->before = $user->score;
        $model->after  = $user->score + $model->score;
    }

    public static function onAfterInsert($model)
    {
        $user = User::where('id', $model->user_id)->find();
        if (!$user) {
            $model->delete();
            throw new Exception("The user can't find it");
        }
        $user->score += $model->score;
        $user->save();
    }

    public static function onBeforeDelete()
    {
        return false;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}