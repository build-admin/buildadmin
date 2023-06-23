<?php

namespace app\admin\controller\auth;

use ba\Tree;
use Exception;
use think\facade\Db;
use app\admin\model\MenuRule;
use app\common\controller\Backend;
use think\db\exception\PDOException;
use think\exception\ValidateException;

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

    protected $preExcludeFields = ['create_time', 'update_time'];

    protected $quickSearchField = 'title';

    /**
     * 远程select初始化传值
     * @var array
     */
    protected $initValue;

    /**
     * 搜索关键词
     * @var array
     */
    protected $keyword = false;

    /**
     * 是否组装Tree
     * @var bool
     */
    protected $assembleTree;

    protected $modelValidate = false;

    public function initialize()
    {
        parent::initialize();
        $this->model = new MenuRule();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get("initValue/a", '');
        $this->keyword   = $this->request->request("quick_search");

        // 有初始化值时不组装树状（初始化出来的值更好看）
        $this->assembleTree = $isTree && !$this->initValue;
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

    /**
     * 编辑
     */
    public function edit()
    {
        $id  = $this->request->param($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                if (isset($data['pid']) && $data['pid'] > 0) {
                    // 满足意图并消除副作用
                    $parent = $this->model->where('id', $data['pid'])->find();
                    if ($parent['pid'] == $row['id']) {
                        $parent->pid = 0;
                        $parent->save();
                    }
                }
                $result = $row->save($data);
                Db::commit();
            } catch (ValidateException|Exception|PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * @param array $ids
     */
    public function del(array $ids = [])
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk      = $this->model->getPk();
        $data    = $this->model->where($pk, 'in', $ids)->select();
        $subData = $this->model->where('pid', 'in', $ids)->column('pid', 'id');
        foreach ($subData as $key => $subDatum) {
            if (!in_array($key, $ids)) {
                $this->error(__('Please delete the child element first, or use batch deletion'));
            }
        }
        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 重写select方法
     */
    public function select()
    {
        $data = $this->getMenus([['type', 'in', ['menu_dir', 'menu']], ['status', '=', '1']]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    protected function getMenus($where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get("initKey/s", $pk);

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

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        // 读取用户组所有权限规则
        $rules = $this->model
            ->where($where)
            ->order('weigh desc,id asc')
            ->select()->toArray();

        // 如果要求树状，此处先组装好 children
        return $this->assembleTree ? $this->tree->assembleChild($rules) : $rules;
    }
}