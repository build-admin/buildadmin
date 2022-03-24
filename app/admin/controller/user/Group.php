<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\UserGroup;

class Group extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = ['updatetime', 'createtime'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserGroup();
    }
}