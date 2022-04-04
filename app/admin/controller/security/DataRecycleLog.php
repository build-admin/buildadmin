<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\DataRecycleLog as DataRecycleLogModel;

class DataRecycleLog extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = [];

    protected $quickSearchField = 'recycle.name';

    protected $withJoinTable = ['recycle', 'admin'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new DataRecycleLogModel();
    }
}