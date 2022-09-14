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
        return full_url($row['url']);
    }

    protected static function onBeforeInsert($model)
    {
        $repeat = $model->where([
            ['sha1', '=', $model->sha1],
            ['topic', '=', $model->topic],
            ['storage', '=', $model->storage],
        ])->find();
        if ($repeat) {
            //判断文件是否存在
            $distPath      = root_path() . 'public' . DIRECTORY_SEPARATOR;
            $storageFile    = $distPath . $repeat['url'];
            if (!file_exists($storageFile)){
                //删除记录
                $repeat->delete();
                return true;
            }else{
                $repeat->quote++;
                $repeat->lastuploadtime = time();
                $repeat->save();
                return false;
            }
        }
        return true;
    }

    protected static function onAfterInsert($model)
    {
        if (!$model->lastuploadtime) {
            $model->quote          = 1;
            $model->lastuploadtime = time();
            $model->save();
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