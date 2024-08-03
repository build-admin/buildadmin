<?php

namespace app\common\model;

use Throwable;
use think\Model;
use think\facade\Event;
use app\admin\model\Admin;
use app\common\library\Upload;
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

    /**
     * 上传类实例，可以通过它调用上传文件驱动，且驱动类具有静态缓存
     */
    protected static Upload $upload;

    protected static function init(): void
    {
        self::$upload = new Upload();
    }

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
        $driver = self::$upload->getDriver($row['storage'], false);
        return $driver ? $driver->url($row['url']) : full_url($row['url']);
    }

    /**
     * 新增前
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
            $driver = self::$upload->getDriver($repeat->storage, false);
            if ($driver && !$driver->exists($repeat->url)) {
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

    /**
     * 新增后
     */
    protected static function onAfterInsert($model): void
    {
        Event::trigger('AttachmentInsert', $model);

        if (!$model->last_upload_time) {
            $model->quote            = 1;
            $model->last_upload_time = time();
            $model->save();
        }
    }

    /**
     * 删除后
     */
    protected static function onAfterDelete($model): void
    {
        Event::trigger('AttachmentDel', $model);

        $driver = self::$upload->getDriver($model->storage, false);
        if ($driver && $driver->exists($model->url)) {
            $driver->delete($model->url);
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