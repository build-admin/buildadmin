<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\User as UserModel;

class User extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = ['lastlogintime', 'loginfailure', 'password', 'salt'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserModel();
    }
}