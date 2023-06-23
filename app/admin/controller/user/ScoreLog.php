<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\UserScoreLog;
use app\admin\model\User;

class ScoreLog extends Backend
{
    protected $model = null;

    protected $withJoinTable = ['user'];

    // 排除字段
    protected $preExcludeFields = ['create_time'];

    protected $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserScoreLog();
    }

    /**
     * 添加
     */
    public function add($userId = 0)
    {
        if ($this->request->isPost()) {
            parent::add();
        }

        $user = User::where('id', (int)$userId)->find();
        if (!$user) {
            $this->error(__("The user can't find it"));
        }
        $this->success('', [
            'user' => $user
        ]);
    }
}