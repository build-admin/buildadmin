<?php

namespace app\admin\controller\auth;

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

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MenuRule();
    }

    public function index()
    {
        $this->success('ok', [
            'menu' => $this->getMenus()
        ]);
    }

    protected function getMenus()
    {
        $rules = $this->getRuleList();
        if (!$rules) {
            return [];
        }
        foreach ($rules as $rule) {
            $this->childrens[$rule['pid']][] = $rule;
        }
        if (!isset($this->childrens[0])) {
            return [];
        }

        return $this->getChildren($this->childrens[0]);
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

    protected function getRuleList()
    {
        $ids = $this->auth->getRuleIds();

        $where = [];
        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }

        // 读取用户组所有权限规则
        $rules = $this->model
            ->where($where)
            ->order('weigh desc,id asc')
            ->select();

        return $rules;
    }
}