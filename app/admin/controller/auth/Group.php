<?php

namespace app\admin\controller\auth;

use app\admin\model\MenuRule;
use ba\Tree;
use app\common\controller\Backend;
use app\admin\model\AdminGroup;
use Exception;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;

class Group extends Backend
{
    /**
     * @var AdminGroup
     */
    protected $model = null;

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'name';

    /**
     * @var Tree
     */
    protected $tree = null;

    protected $keyword = false;

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminGroup();
        $this->tree  = Tree::instance();

        $this->keyword = $this->request->request("quick_search");
    }

    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getGroups(),
            'remark' => get_route_remark(),
        ]);
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
                $rules      = MenuRule::select();
                $superAdmin = true;
                foreach ($rules as $rule) {
                    if (!in_array($rule['id'], $data['rules'])) {
                        $superAdmin = false;
                    }
                }

                if ($superAdmin) {
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
                $rules      = MenuRule::select();
                $superAdmin = true;
                foreach ($rules as $rule) {
                    if (!in_array($rule['id'], $data['rules'])) {
                        $superAdmin = false;
                    }
                }

                if ($superAdmin) {
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

        $row->pid   = $row->pid ? $this->model->where('id', $row->pid)->value('name') : '';
        $row->rules = $row->rules ? explode(',', $row->rules) : [];
        $this->success('', [
            'row' => $row
        ]);
    }

    public function select()
    {
        $isTree = $this->request->param('isTree');
        $data   = $this->getGroups();

        if ($isTree && !$this->keyword) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'name'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    public function getGroups()
    {
        $where = [];
        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        $data = $this->model->where('status', '1')->where($where)->select();
        // 获取第一个权限的名称
        foreach ($data as $datum) {
            if ($datum->rules) {
                if ($datum->rules == '*') {
                    $datum->rules = '超级管理员';
                } else {
                    $rules = explode(',', $datum->rules);
                    if ($rules) {
                        $rulesFirstTitle = MenuRule::where('id', $rules[0])->value('title');
                        $datum->rules    = count($rules) == 1 ? $rulesFirstTitle : $rulesFirstTitle . '等 ' . count($rules) . ' 项';
                    }
                }
            } else {
                $datum->rules = '无权限';
            }
        }
        return $this->tree->assembleChild($data);
    }

}