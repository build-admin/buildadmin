<?php

use think\facade\Db;
use app\admin\model\Config;
use think\migration\Migrator;

class Version205 extends Migrator
{
    public function up(): void
    {
        $configQuickEntrance = Config::where('name', 'config_quick_entrance')->find();
        $value               = $configQuickEntrance->value;
        foreach ($value as &$item) {
            if (str_starts_with($item['value'], '/admin/')) {
                $pathData = Db::name('admin_rule')->where('path', substr($item['value'], 7))->find();
                if ($pathData) {
                    $item['value'] = $pathData['name'];
                }
            }
        }
        $configQuickEntrance->value = $value;
        $configQuickEntrance->save();
    }
}
