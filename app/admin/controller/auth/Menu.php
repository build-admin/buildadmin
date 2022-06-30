<?php

namespace app\admin\controller\auth;

use ba\Tree;
use app\admin\model\MenuRule;
use app\common\controller\Backend;

class Menu extends Backend
{
    /**
     * @var MenuRule
     */
    protected $model = null;

    /**
     * @var Tree
     */
    protected $tree = null;

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'title';

    protected $keyword = false;

    protected $modelValidate = false;

    public function initialize()
    {
        parent::initialize();
        $this->model = new MenuRule();
        $this->tree  = Tree::instance();

        $this->keyword = $this->request->request("quick_search");
    }

    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getMenus(),
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

    /**
     * 重写select方法
     */
    public function select()
    {
        $isTree = $this->request->param('isTree');
        $data   = $this->getMenus([['type', 'in', ['menu_dir', 'menu']], ['status', '=', '1']]);

        if ($isTree && !$this->keyword) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    protected function getMenus($where = [])
    {
        $rules = $this->getRuleList($where);
        return $this->tree->assembleChild($rules);
    }

    protected function getRuleList($where = [])
    {
        $ids = $this->auth->getRuleIds();

        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }

        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        // 读取用户组所有权限规则
        $rules = $this->model
            ->where($where)
            ->order('weigh desc,id asc')
            ->select();

        return $rules;
    }
}