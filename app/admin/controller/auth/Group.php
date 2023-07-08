<?php

namespace app\admin\controller\auth;

use ba\Tree;
use Throwable;
use think\facade\Db;
use app\admin\model\AdminRule;
use app\admin\model\AdminGroup;
use app\common\controller\Backend;

class Group extends Backend
{
    /**
     * 修改、删除分组时对操作管理员进行鉴权
     * 本管理功能部分场景对数据权限有要求，修改此值请额外确定以下的 absoluteAuth 实现的功能
     * allAuthAndOthers=管理员拥有该分组所有权限并拥有额外权限时允许
     */
    protected string $authMethod = 'allAuthAndOthers';

    /**
     * 数据模型
     * @var object
     * @phpstan-var AdminGroup
     */
    protected object $model;

    protected string|array $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = 'name';

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
     * 登录管理员的角色组
     * @var array
     */
    protected array $adminGroups = [];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AdminGroup();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get("initValue/a", []);
        $this->initValue = array_filter($this->initValue);
        $this->keyword   = $this->request->request("quickSearch", '');

        // 有初始化值时不组装树状（初始化出来的值更好看）
        $this->assembleTree = $isTree && !$this->initValue;

        $this->adminGroups = Db::name('admin_group_access')->where('uid', $this->auth->id)->column('group_id');
    }

    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getGroups(),
            'group'  => $this->adminGroups,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            $data = $this->handleRules($data);

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        $validate->scene('add')->check($data);
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @param string|null $id
     * @return void
     * @throws Throwable
     */
    public function edit(string $id = null): void
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $this->checkAuth($id);

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $adminGroup = Db::name('admin_group_access')->where('uid', $this->auth->id)->column('group_id');
            if (in_array($data['id'], $adminGroup)) {
                $this->error(__('You cannot modify your own management group!'));
            }

            $data = $this->excludeFields($data);
            $data = $this->handleRules($data);

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        $validate->scene('edit')->check($data);
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

        // 读取所有pid，全部从节点数组移除，父级选择状态由子级决定
        $pidArr = AdminRule::field('pid')
            ->distinct(true)
            ->where('id', 'in', $row->rules)
            ->select()->toArray();
        $rules  = $row->rules ? explode(',', $row->rules) : [];
        foreach ($pidArr as $item) {
            $ruKey = array_search($item['pid'], $rules);
            if ($ruKey !== false) {
                unset($rules[$ruKey]);
            }
        }
        $row->rules = array_values($rules);
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

        $pk   = $this->model->getPk();
        $data = $this->model->where($pk, 'in', $ids)->select();
        foreach ($data as $v) {
            $this->checkAuth($v->id);
        }
        $subData = $this->model->where('pid', 'in', $ids)->column('pid', 'id');
        foreach ($subData as $key => $subDatum) {
            if (!in_array($key, $ids)) {
                $this->error(__('Please delete the child element first, or use batch deletion'));
            }
        }

        $adminGroup = Db::name('admin_group_access')->where('uid', $this->auth->id)->column('group_id');
        $count      = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                if (!in_array($v['id'], $adminGroup)) {
                    $count += $v->delete();
                }
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
     * @return void
     * @throws Throwable
     */
    public function select(): void
    {
        $data = $this->getGroups([['status', '=', 1]]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    /**
     * 权限节点入库前处理
     * @throws Throwable
     */
    public function handleRules(array &$data): array
    {
        if (!empty($data['rules']) && is_array($data['rules'])) {
            $rules      = AdminRule::select();
            $superAdmin = true;
            foreach ($rules as $rule) {
                if (!in_array($rule['id'], $data['rules'])) {
                    $superAdmin = false;
                }
            }

            if ($superAdmin) {
                $data['rules'] = '*';
            } else {
                // 禁止添加`拥有自己全部权限`的分组
                if (!array_diff($this->auth->getRuleIds(), $data['rules'])) {
                    $this->error(__('Role group has all your rights, please contact the upper administrator to add or do not need to add!'));
                }
                $data['rules'] = implode(',', $data['rules']);
            }
        } else {
            unset($data['rules']);
        }
        return $data;
    }

    /**
     * 获取分组
     * @param array $where
     * @return array
     * @throws Throwable
     */
    public function getGroups(array $where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get("initKey/s", $pk);

        // 下拉选择时只获取：拥有所有权限并且有额外权限的分组
        $absoluteAuth = $this->request->get('absoluteAuth/b', false);

        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        if (!$this->auth->isSuperAdmin()) {
            $authGroups = $this->auth->getAllAuthGroups($this->authMethod);
            if (!$absoluteAuth) $authGroups = array_merge($this->adminGroups, $authGroups);
            $where[] = ['id', 'in', $authGroups];
        }
        $data = $this->model->where($where)->select()->toArray();

        // 获取第一个权限的名称供列表显示-s
        foreach ($data as &$datum) {
            if ($datum['rules']) {
                if ($datum['rules'] == '*') {
                    $datum['rules'] = __('Super administrator');
                } else {
                    $rules = explode(',', $datum['rules']);
                    if ($rules) {
                        $rulesFirstTitle = AdminRule::where('id', $rules[0])->value('title');
                        $datum['rules']  = count($rules) == 1 ? $rulesFirstTitle : $rulesFirstTitle . '等 ' . count($rules) . ' 项';
                    }
                }
            } else {
                $datum['rules'] = __('No permission');
            }
        }
        // 获取第一个权限的名称供列表显示-e

        // 如果要求树状，此处先组装好 children
        return $this->assembleTree ? $this->tree->assembleChild($data) : $data;
    }

    /**
     * 检查权限
     * @param $groupId
     * @return void
     * @throws Throwable
     */
    public function checkAuth($groupId): void
    {
        $authGroups = $this->auth->getAllAuthGroups($this->authMethod);
        if (!$this->auth->isSuperAdmin() && !in_array($groupId, $authGroups)) {
            $this->error(__($this->authMethod == 'allAuth' ? 'You need to have all permissions of this group to operate this group~' : 'You need to have all the permissions of the group and have additional permissions before you can operate the group~'));
        }
    }

}