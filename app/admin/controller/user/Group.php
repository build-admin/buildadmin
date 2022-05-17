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
    protected $preExcludeFields = ['updatetime', 'createtime'];

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
                    unset($data['half_rules']);
                } else {
                    $data['rules']      = implode(',', $data['rules']);
                    $data['half_rules'] = implode(',', $data['half_rules']);
                }
            } else {
                unset($data['rules']);
                unset($data['half_rules']);
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
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
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
                    $data['rules']      = '*';
                    $data['half_rules'] = '';
                } else {
                    $data['rules']      = implode(',', $data['rules']);
                    $data['half_rules'] = implode(',', $data['half_rules']);
                }
            } else {
                unset($data['rules']);
                unset($data['half_rules']);
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
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $rules           = $row->rules ? explode(',', $row->rules) : [];
        $row->half_rules = $row->half_rules ? explode(',', $row->half_rules) : [];
        foreach ($row->half_rules as $half_rule) {
            $ruKey = array_search($half_rule, $rules);
            if ($ruKey) {
                unset($rules[$ruKey]);
            }
        }
        $row->rules = $rules;
        $this->success('', [
            'row' => $row
        ]);
    }
}