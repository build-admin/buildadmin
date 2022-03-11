<?php

namespace app\admin\model;

use think\facade\Config;
use think\Model;
use ba\Random;

class Admin extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastlogintimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '无';
    }

    /**
     * 重置用户密码
     * @param int    $uid         管理员ID
     * @param string $newPassword 新密码
     */
    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = self::encryptPassword($newPassword, $salt);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
        return $ret;
    }

    /**
     * 加密密码
     */
    public static function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($encrypt($password) . $salt);
    }
}