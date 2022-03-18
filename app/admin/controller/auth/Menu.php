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

    protected $noNeedPermission = ['index'];
    protected $childrens        = [];

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'title';

    protected $keyword = false;

    public function initialize()
    {
        parent::initialize();
        $this->model = new MenuRule();
    }

    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->keyword = $this->request->request("quick_search");
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

        $row->pid = $row->pid ? $this->model->where('id', $row->pid)->value('title') : '';
        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 重写select方法
     */
    public function select()
    {
        $isTree        = $this->request->param('isTree');
        $this->keyword = $this->request->request("quick_search");
        $data          = $this->getMenus(false);

        if ($isTree && !$this->keyword) {
            $data = Tree::assembleTree(Tree::getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    protected function getMenus($getButton = true)
    {
        $rules = $this->getRuleList($getButton);
        if (!$rules) {
            return [];
        }
        foreach ($rules as $rule) {
            $this->childrens[$rule['pid']][] = $rule;
        }
        if (isset($this->childrens[0])) {
            return $this->getChildren($this->childrens[0]);
        } else {
            return $rules;
        }
    }

    protected function getChildren($rules): array
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($rule['id'], $this->childrens)) {
                $rules[$key]['children'] = $this->getChildren($this->childrens[$rule['id']]);
            }
        }
        return $rules;
    }

    protected function getRuleList($getButton = true)
    {
        $ids = $this->auth->getRuleIds();

        $where = [];
        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }
        if (!$getButton) {
            $where[] = ['type', 'in', ['menu_dir', 'menu']];
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