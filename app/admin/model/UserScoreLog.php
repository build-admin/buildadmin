<?php

namespace app\admin\model;

use Throwable;
use think\model;
use think\Exception;
use think\model\relation\BelongsTo;

/**
 * UserScoreLog 模型
 * 1. 创建积分日志自动完成会员积分的添加
 * 2. 创建积分日志时，请开启事务
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
        $user = User::where('id', $model->user_id)->lock(true)->find();
        if (!$user) {
            throw new Exception("The user can't find it");
        }
        if (!$model->memo) {
            throw new Exception("Change note cannot be blank");
        }
        $model->before = $user->score;

        $user->score += $model->score;
        $user->save();

        $model->after = $user->score;
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