<?php

namespace app\admin\model;

use ba\Random;
use think\Model;
use think\facade\Config;
use app\admin\model\UserGroup;

/**
 * User 模型
 * @controllerUrl 'userUser'
 */
class User extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    /**
     * 重置用户密码
     * @param int    $uid         用户ID
     * @param string $newPassword 新密码
     */
    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
        return $ret;
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }
}