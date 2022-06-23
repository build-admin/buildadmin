<?php

namespace app\common\model;

use ba\Random;
use think\Model;
use think\facade\Config;

class User extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
        return $ret;
    }
}