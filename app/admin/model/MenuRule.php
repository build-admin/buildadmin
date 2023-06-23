<?php

namespace app\admin\model;

use think\Model;

/**
 * MenuRule 模型
 * @controllerUrl 'authMenu'
 */
class MenuRule extends Model
{
    protected $autoWriteTimestamp = true;

    public function setComponentAttr($value)
    {
        if ($value) $value = str_replace('\\', '/', $value);
        return $value;
    }

}