<?php

namespace app\admin\model;

use ba\Random;
use think\Model;
use think\facade\Db;
use think\facade\Config;

/**
 * Admin模型
 * @controllerUrl 'authAdmin'
 */
class Admin extends Model
{
    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = true;

    /**
     * 追加属性
     */
    protected $append = [
        'group_arr',
        'group_name_arr',
    ];

    public function getGroupArrAttr($value, $row): array
    {
        return Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
    }

    public function getGroupNameArrAttr($value, $row): array
    {
        $groupAccess = Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        return AdminGroup::whereIn('id', $groupAccess)->column('name');
    }

    public function getAvatarAttr($value): string
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastLoginTimeAttr($value): string
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 重置用户密码
     * @param int|string $uid         管理员ID
     * @param string     $newPassword 新密码
     * @return Admin
     */
    public function resetPassword(int|string $uid, string $newPassword): Admin
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }
}