<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\SensitiveDataLog as SensitiveDataLogModel;

class SensitiveDataLog extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = [];

    protected $quickSearchField = 'sensitive.name';

    protected $withJoinTable = ['sensitive', 'admin'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new SensitiveDataLogModel();
    }
}