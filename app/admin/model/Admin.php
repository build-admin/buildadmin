<?php

namespace app\admin\model;

use think\facade\Config;
use think\facade\Db;
use think\Model;
use ba\Random;
use app\admin\model\AdminGroup;

/**
 * Admin模型
 * @controllerUrl 'authAdmin'
 */
class Admin extends Model
{
    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = 'int';

    /**
     * @var string 自动写入创建时间
     */
    protected $createTime = 'createtime';
    /**
     * @var string 自动写入更新时间
     */
    protected $updateTime = 'updatetime';

    /**
     * 追加属性
     */
    protected $append = [
        'group_arr',
        'group_name_arr',
    ];

    public function getGroupArrAttr($value, $row)
    {
        $groupAccess = Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        return $groupAccess;
    }

    public function getGroupNameArrAttr($value, $row)
    {
        $groupAccess = Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        $groupNames  = AdminGroup::whereIn('id', $groupAccess)->column('name');
        return $groupNames;
    }

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastlogintimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : 'none';
    }

    /**
     * 重置用户密码
     * @param int    $uid         管理员ID
     * @param string $newPassword 新密码
     */
    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
        return $ret;
    }
}