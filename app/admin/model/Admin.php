<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 重置用户密码 - 待完善
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = self::encryptPassword($NewPassword);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    // 密码加密
    public static function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($encrypt($password) . $salt);
    }
}