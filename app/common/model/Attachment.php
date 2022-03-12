<?php

namespace app\common\model;

use think\Model;

class Attachment extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = null;

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
}