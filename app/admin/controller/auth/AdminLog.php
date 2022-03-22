<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use app\admin\model\AdminLog as AdminLogModel;

class AdminLog extends Backend
{
    /**
     * @var AdminLogModel
     */
    protected $model = null;

    protected $preExcludeFields = ['createtime', 'admin_id', 'username'];

    protected $quickSearchField = ['title'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminLogModel();
    }
}