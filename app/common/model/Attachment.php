<?php

namespace app\common\model;

use Throwable;
use think\Model;
use ba\Filesystem;
use app\admin\model\Admin;
use think\model\relation\BelongsTo;

/**
 * Attachment模型
 */
class Attachment extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    protected $append = [
        'suffix',
        'full_url'
    ];

    public function getSuffixAttr($value, $row): string
    {
        if ($row['name']) {
            $suffix = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
            return $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        }
        return 'file';
    }

    public function getFullUrlAttr($value, $row): string
    {
        return full_url($row['url']);
    }

    /**
     * 入库前
     * @throws Throwable
     */
    protected static function onBeforeInsert($model): bool
    {
        $repeat = $model->where([
            ['sha1', '=', $model->sha1],
            ['topic', '=', $model->topic],
            ['storage', '=', $model->storage],
        ])->find();
        if ($repeat) {
            $storageFile = Filesystem::fsFit(public_path() . ltrim($repeat['url'], '/'));
            if ($model->storage == 'local' && !file_exists($storageFile)) {
                $repeat->delete();
                return true;
            } else {
                $repeat->quote++;
                $repeat->last_upload_time = time();
                $repeat->save();
                return false;
            }
        }
        return true;
    }

    protected static function onAfterInsert($model)
    {
        if (!$model->last_upload_time) {
            $model->quote            = 1;
            $model->last_upload_time = time();
            $model->save();
        }
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}