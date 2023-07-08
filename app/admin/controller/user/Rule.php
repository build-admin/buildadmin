<?php

namespace app\admin\controller\user;

use ba\Tree;
use Throwable;
use app\admin\model\UserRule;
use app\common\controller\Backend;

class Rule extends Backend
{
    /**
     * @var object
     * @phpstan-var UserRule
     */
    protected object $model;

    /**
     * @var Tree
     */
    protected Tree $tree;

    protected array $noNeedLogin = ['index'];

    protected string|array $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = 'title';

    /**
     * 远程select初始化传值
     * @var array
     */
    protected array $initValue;

    /**
     * 是否组装Tree
     * @var bool
     */
    protected bool $assembleTree;

    /**
     * 搜索关键词
     * @var string
     */
    protected string $keyword;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserRule();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get("initValue/a", []);
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
            'list'   => $this->getRules(),
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
     * 远程下拉
     * @throws Throwable
     */
    public function select(): void
    {
        $data = $this->getRules([['status', '=', '1']]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    /**
     * 获取菜单规则
     * @throws Throwable
     */
    public function getRules(array $where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get("initKey/s", $pk);

        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        $data = $this->model->where($where)->order('weigh desc,id asc')->select()->toArray();
        return $this->assembleTree ? $this->tree->assembleChild($data) : $data;
    }

}