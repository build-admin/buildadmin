<?php

namespace app\admin\controller\user;

use ba\Tree;
use app\admin\model\UserRule;
use app\common\controller\Backend;

class Rule extends Backend
{
    /**
     * @var UserRule
     */
    protected $model = null;

    /**
     * @var Tree
     */
    protected $tree = null;

    protected $noNeedLogin = ['index'];

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'title';

    protected $keyword = false;

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserRule();
        $this->tree  = Tree::instance();

        $this->keyword = $this->request->request("quick_search");
    }

    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getRule(),
            'remark' => get_route_remark(),
        ]);
    }

    public function edit($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            parent::edit($id);
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    public function select()
    {
        $isTree = $this->request->param('isTree');
        $data   = $this->getRule();

        if ($isTree && !$this->keyword) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    public function getRule()
    {
        $where = [];
        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        $data = $this->model->where($where)->order('weigh desc,id asc')->select()->toArray();
        return $this->tree->assembleChild($data);
    }

}