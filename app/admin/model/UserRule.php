<?php

namespace app\admin\model;

use think\model;

/**
 * UserRule 模型
 */
class UserRule extends model
{
    protected $autoWriteTimestamp = true;

    protected static function onAfterInsert($model)
    {
        $pk = $model->getPk();
        $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
    }

    public function setComponentAttr($value)
    {
        if ($value) $value = str_replace('\\', '/', $value);
        return $value;
    }
}