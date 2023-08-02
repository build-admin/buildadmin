<?php

namespace app\admin\controller\security;

use Throwable;
use app\common\controller\Backend;
use app\admin\model\SensitiveData as SensitiveDataModel;

class SensitiveData extends Backend
{
    /**
     * @var object
     * @phpstan-var SensitiveDataModel
     */
    protected object $model;

    // 排除字段
    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected string|array $quickSearchField = 'controller';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new SensitiveDataModel();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        foreach ($res->items() as $item) {
            if ($item->data_fields) {
                $fields = [];
                foreach ($item->data_fields as $key => $field) {
                    $fields[] = $field ?: $key;
                }
                $item->data_fields = $fields;
            }
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加重写
     */
    public function add(): void
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
            $this->model->startTrans();
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

                if (is_array($data['fields'])) {
                    $data['data_fields'] = [];
                    foreach ($data['fields'] as $field) {
                        $data['data_fields'][$field['name']] = $field['value'];
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

        // 放在add方法内，就不需要额外添加权限节点了
        $this->success('', [
            'tables'      => $this->getTableList(),
            'controllers' => $this->getControllerList(),
        ]);
    }

    /**
     * 编辑重写
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

            $data                  = $this->excludeFields($data);
            $data['controller_as'] = str_ireplace('.php', '', $data['controller'] ?? '');
            $data['controller_as'] = strtolower(str_ireplace(['\\', '.'], '/', $data['controller_as']));

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

                if (is_array($data['fields'])) {
                    $data['data_fields'] = [];
                    foreach ($data['fields'] as $field) {
                        $data['data_fields'][$field['name']] = $field['value'];
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
            'row'         => $row,
            'tables'      => $this->getTableList(),
            'controllers' => $this->getControllerList(),
        ]);
    }

    protected function getControllerList(): array
    {
        $outExcludeController = [
            'Addon.php',
            'Ajax.php',
            'Dashboard.php',
            'Index.php',
            'Module.php',
            'Terminal.php',
            'auth/AdminLog.php',
            'routine/AdminInfo.php',
            'routine/Config.php',
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

    protected function getTableList(): array
    {
        $tablePrefix     = config('database.connections.mysql.prefix');
        $outExcludeTable = [
            // 功能表
            'area',
            'token',
            'captcha',
            'admin_group_access',
            'config',
            // 无编辑功能
            'admin_log',
            'user_money_log',
            'user_score_log',
        ];

        $outTables = [];
        $tables    = get_table_list();
        $pattern   = '/^' . $tablePrefix . '/i';
        foreach ($tables as $table => $tableComment) {
            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $outExcludeTable)) {
                $outTables[$table] = $tableComment;
            }
        }
        return $outTables;
    }
}