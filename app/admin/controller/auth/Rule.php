<?php

namespace app\admin\controller\auth;

use ba\Tree;
use Throwable;
use app\admin\model\AdminRule;
use app\common\controller\Backend;

class Rule extends Backend
{
    protected string|array $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = 'title';

    /**
     * @var object
     * @phpstan-var AdminRule
     */
    protected object $model;

    /**
     * @var Tree
     */
    protected Tree $tree;

    /**
     * 远程select初始化传值
     * @var array
     */
    protected array $initValue;

    /**
     * 搜索关键词
     * @var string
     */
    protected string $keyword;

    /**
     * 是否组装Tree
     * @var bool
     */
    protected bool $assembleTree;

    /**
     * 开启模型验证
     * @var bool
     */
    protected bool $modelValidate = false;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AdminRule();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get('initValue/a', []);
        $this->initValue = array_filter($this->initValue);
        $this->keyword   = $this->request->request('quickSearch', '');

        // 有初始化值时不组装树状（初始化出来的值更好看）
        $this->assembleTree = $isTree && !$this->initValue;
    }

    public function index(): void
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
     * @throws Throwable
     */
    public function edit(): void
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
            $this->model->startTrans();
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
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
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
     * @throws Throwable
     */
    public function del(array $ids = []): void
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
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
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
     * @throws Throwable
     */
    public function select(): void
    {
        $data = $this->getMenus([['type', 'in', ['menu_dir', 'menu']], ['status', '=', '1']]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    /**
     * 获取菜单列表
     * @throws Throwable
     */
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