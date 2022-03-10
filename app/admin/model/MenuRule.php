<?php

namespace app\admin\model;

use think\Model;

class MenuRule extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function onAfterInsert($row)
    {
        $pk = $row->getPk();
        $row->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
    }
}