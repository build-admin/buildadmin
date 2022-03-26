<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\UserScoreLog;

class ScoreLog extends Backend
{
    protected $model = null;

    protected $withJoinTable = ['user'];

    // 排除字段
    protected $preExcludeFields = ['createtime'];

    protected $quickSearchField = ['username', 'nickname'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserScoreLog();
    }
}