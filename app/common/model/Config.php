<?php

namespace app\common\model;

use think\Model;
use think\facade\Cache;
use app\admin\model\Config as adminConfigModel;

class Config extends Model
{
    /**
     * 添加系统配置分组
     */
    public static function addConfigGroup(string $key, string $value): bool
    {
        $configGroupRow = adminConfigModel::where('name', 'config_group')->find();
        foreach ($configGroupRow->value as $item) {
            if ($item['key'] == $key) {
                return false;
            }
        }
        $configGroupRow->value = array_merge($configGroupRow->value, [['key' => $key, 'value' => $value]]);
        $configGroupRow->save();
        Cache::tag(adminConfigModel::$cacheTag)->clear();
        return true;
    }

    /**
     * 删除系统配置分组
     */
    public static function removeConfigGroup(string $key)
    {
        $configGroupRow = adminConfigModel::where('name', 'config_group')->find();
        $configGroup    = $configGroupRow->value;
        foreach ($configGroup as $iKey => $item) {
            if ($item['key'] == $key) {
                unset($configGroup[$iKey]);
            }
        }
        $configGroupRow->value = $configGroup;
        $configGroupRow->save();
        Cache::tag(adminConfigModel::$cacheTag)->clear();
    }

}