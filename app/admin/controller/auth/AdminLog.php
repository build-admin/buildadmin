<?php

namespace app\admin\controller\auth;

use Throwable;
use app\common\controller\Backend;
use app\admin\model\AdminLog as AdminLogModel;

class AdminLog extends Backend
{
    /**
     * @var object
     * @phpstan-var AdminLogModel
     */
    protected object $model;

    protected string|array $preExcludeFields = ['create_time', 'admin_id', 'username'];

    protected string|array $quickSearchField = ['title'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AdminLogModel();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        if (!$this->auth->isSuperAdmin()) {
            $where[] = ['admin_id', '=', $this->auth->id];
        }
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}