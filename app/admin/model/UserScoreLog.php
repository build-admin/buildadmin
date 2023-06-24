<?php

namespace app\admin\model;

use Throwable;
use think\model;
use think\Exception;
use think\model\relation\BelongsTo;

/**
 * UserScoreLog 模型
 */
class UserScoreLog extends model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    /**
     * 入库前
     * @throws Throwable
     */
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

    /**
     * 入库后
     * @throws Throwable
     */
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

    public static function onBeforeDelete(): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}