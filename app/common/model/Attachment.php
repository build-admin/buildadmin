<?php

namespace app\common\model;

use think\Model;
use app\admin\model\Admin;

/**
 * Attachment模型
 * @controllerUrl 'routineAttachment'
 */
class Attachment extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = null;

    protected $append = [
        'suffix',
        'full_url'
    ];

    public function getSuffixAttr($value, $row)
    {
        if ($row['name']) {
            $suffix = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
            return $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        }
        return 'file';
    }

    public function getFullUrlAttr($value, $row)
    {
        return full_url($row['url'], true);
    }

    protected static function onBeforeInsert($model)
    {
        $repeat = $model->where([
            ['sha1', '=', $model->sha1],
            ['topic', '=', $model->topic],
            ['storage', '=', $model->storage],
        ])->find();
        if ($repeat) {
            $repeat->quote++;
            $repeat->lastuploadtime = time();
            $repeat->save();
            return false;
        }
    }

    protected static function onAfterInsert($row)
    {
        if (!$row->lastuploadtime) {
            $row->quote          = 1;
            $row->lastuploadtime = time();
            $row->save();
        }
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}