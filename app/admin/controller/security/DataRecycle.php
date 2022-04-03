<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\DataRecycle as DataRecycleModel;
use think\facade\Db;

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
            parent::add();
        }

        // 放在add方法内，就不需要额外添加权限节点了
        $this->success('', [
            'tables'      => $this->getTableList(),
            'controllers' => $this->getControllerList(),
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
            $tablePrefix . 'token',
            $tablePrefix . 'captcha',
            $tablePrefix . 'admin_group_access',
            // 无删除功能
            $tablePrefix . 'user_money_log',
            $tablePrefix . 'user_score_log',
        ];

        $outTables = [];
        $tables    = get_table_list();
        foreach ($tables as $key => $table) {
            if (!in_array($table, $outExcludeTable)) {
                $outTables[$key] = $table;
            }
        }
        return $outTables;
    }

    public function getPk($table = null)
    {
        $tablePk = Db::table($table)->getPk();
        $this->success('ok', ['pk' => $tablePk]);
    }
}