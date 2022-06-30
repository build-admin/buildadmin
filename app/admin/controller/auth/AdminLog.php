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

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
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