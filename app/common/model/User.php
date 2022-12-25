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
        return full_url(htmlspecialchars_decode($value), true, Config::get('buildadmin.default_avatar'));
    }

    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }

    public function getMoneyAttr($value)
    {
        return bcdiv($value, 100, 2);
    }

    /**
     * 用户的余额是不可以直接进行修改的，请通过 UserMoneyLog 模型插入记录来实现自动修改余额
     * 此处定义上 money 的修改器仅为防止直接对余额的修改造成数据错乱
     */
    public function setMoneyAttr($value)
    {
        return bcmul($value, 100, 2);
    }
}