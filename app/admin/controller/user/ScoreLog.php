<?php

namespace app\admin\controller\user;

use Throwable;
use app\admin\model\User;
use app\admin\model\UserScoreLog;
use app\common\controller\Backend;

class ScoreLog extends Backend
{
    /**
     * @var object
     * @phpstan-var UserScoreLog
     */
    protected object $model;

    protected array $withJoinTable = ['user'];

    // 排除字段
    protected string|array $preExcludeFields = ['create_time'];

    protected string|array $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserScoreLog();
    }

    /**
     * 添加
     * @param int $userId
     * @throws Throwable
     */
    public function add(int $userId = 0): void
    {
        if ($this->request->isPost()) {
            parent::add();
        }

        $user = User::where('id', $userId)->find();
        if (!$user) {
            $this->error(__("The user can't find it"));
        }
        $this->success('', [
            'user' => $user
        ]);
    }
}