<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\DataRecycle as DataRecycleModel;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;
use Exception;

class DataRecycle extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = ['updatetime', 'createtime'];

    protected $quickSearchField = 'name';

    public function initialize()
    {
        parent::initialize();
        $this->model = new DataRecycleModel();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data                  = $this->excludeFields($data);
            $data['controller_as'] = str_ireplace('.php', '', $data['controller'] ?? '');
            $data['controller_as'] = strtolower(str_ireplace(['\\', '.'], '/', $data['controller_as']));

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
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

        // 放在add方法内，就不需要额外添加权限节点了
        $this->success('', [
            'tables'      => $this->getTableList(),
            'controllers' => $this->getControllerList(),
        ]);
    }

    /**
     * 编辑
     * @param null $id
     */
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

            $data                  = $this->excludeFields($data);
            $data['controller_as'] = str_ireplace('.php', '', $data['controller'] ?? '');
            $data['controller_as'] = strtolower(str_ireplace(['\\', '.'], '/', $data['controller_as']));

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

        $this->success('', [
            'row' => $row
        ]);
    }

    protected function getControllerList()
    {
        $outExcludeController = [
            'Addon.php',
            'Ajax.php',
            'Dashboard.php',
            'Index.php',
            'routine/AdminInfo.php',
            'user/MoneyLog.php',
            'user/ScoreLog.php',
        ];
        $outControllers       = [];
        $controllers          = get_controller_list();
        foreach ($controllers as $key => $controller) {
            if (!in_array($controller, $outExcludeController)) {
                $outControllers[$key] = $controller;
            }
        }
        return $outControllers;
    }

    protected function getTableList()
    {
        $tablePrefix     = config('database.connections.mysql.prefix');
        $outExcludeTable = [
            // 功能表
            'token',
            'captcha',
            'admin_group_access',
            // 无删除功能
            'user_money_log',
            'user_score_log',
        ];

        $outTables = [];
        $tables    = get_table_list();
        $pattern   = '/^' . $tablePrefix . '/i';
        foreach ($tables as $table) {
            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $outExcludeTable)) {
                $outTables[$table] = $table;
            }
        }
        return $outTables;
    }
}