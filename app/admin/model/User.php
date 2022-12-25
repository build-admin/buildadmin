<?php

namespace app\admin\model;

use ba\Random;
use think\Model;

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
        return htmlspecialchars_decode($value);
    }

    public function getMoneyAttr($value)
    {
        return bcdiv($value, 100, 2);
    }

    public function setMoneyAttr($value)
    {
        return bcmul($value, 100, 2);
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
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }
}