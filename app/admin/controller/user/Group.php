<?php

namespace app\admin\controller\user;

use Exception;
use think\facade\Db;
use app\admin\model\UserRule;
use app\admin\model\UserGroup;
use app\common\controller\Backend;
use think\db\exception\PDOException;
use think\exception\ValidateException;

class Group extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = ['update_time', 'create_time'];

    protected $quickSearchField = 'name';

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserGroup();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
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

            $result = false;
            Db::startTrans();
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
                Db::commit();
            } catch (ValidateException|Exception|PDOException $e) {
                Db::rollback();
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

    public function edit($id = null)
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

            $result = false;
            Db::startTrans();
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

        // 读取所有pid，全部从节点数组移除，父级选择状态由子级决定
        $pids  = UserRule::field('pid')
            ->distinct(true)
            ->where('id', 'in', $row->rules)
            ->select()->toArray();
        $rules = $row->rules ? explode(',', $row->rules) : [];
        foreach ($pids as $item) {
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
}