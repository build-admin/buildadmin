<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use app\admin\model\AdminGroup;

class Group extends Backend
{
    /**
     * @var AdminGroup
     */
    protected $model = null;

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'name';

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminGroup();
    }

}