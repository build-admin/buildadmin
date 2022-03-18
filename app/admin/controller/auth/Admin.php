<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use app\admin\model\Admin as AdminModel;

class Admin extends Backend
{
    /**
     * @var AdminModel
     */
    protected $model = null;

    protected $preExcludeFields = ['createtime', 'updatetime', 'password', 'salt', 'loginfailure', 'lastlogintime', 'lastloginip'];

    protected $quickSearchField = ['username', 'nickname'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminModel();
    }
}