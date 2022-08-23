<?php

namespace app\admin\model;

use think\model;

/**
 * UserRule 模型
 * @controllerUrl 'userRule'
 */
class UserRule extends model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function onAfterInsert($model)
    {
        $pk = $model->getPk();
        $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
    }
}