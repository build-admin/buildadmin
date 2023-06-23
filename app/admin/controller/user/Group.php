<?php

namespace app\admin\controller\user;

use Throwable;
use app\admin\model\UserRule;
use app\admin\model\UserGroup;
use app\common\controller\Backend;

class Group extends Backend
{
    /**
     * @var object
     * @phpstan-var UserGroup
     */
    protected object $model;

    // 排除字段
    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected string|array $quickSearchField = 'name';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserGroup();
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
     * @param string|int|null $id
     * @throws Throwable
     */
    public function edit(string|int $id = null): void
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

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
        $pidArr = UserRule::field('pid')
            ->distinct(true)
            ->where('id', 'in', $row->rules)
            ->select()
            ->toArray();
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
     * 权限规则入库前处理
     * @param array $data 接受到的数据
     * @return array
     * @throws Throwable
     */
    public function handleRules(array &$data): array
    {
        if (is_array($data['rules']) && $data['rules']) {
            $rules = UserRule::select();
            $super = true;
            foreach ($rules as $rule) {
                if (!in_array($rule['id'], $data['rules'])) {
                    $super = false;
                }
            }

            if ($super) {
                $data['rules'] = '*';
            } else {
                $data['rules'] = implode(',', $data['rules']);
            }
        } else {
            unset($data['rules']);
        }
        return $data;
    }
}