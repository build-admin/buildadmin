<?php

namespace app\admin\controller\routine;

use app\common\controller\Backend;
use app\admin\model\Config as ConfigModel;

class Config extends Backend
{
    protected $model = null;

    protected $noNeedLogin = ['index'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index()
    {
        $configGroup = get_sys_config('config_group');
        $config      = $this->model->select();
        $list        = [];
        foreach ($config as $item) {
            $list[$item['group']]['list'][] = $item;
        }
        foreach ($configGroup as $key => $item) {
            $list[$key]['name']  = $key;
            $list[$key]['title'] = __($item);
        }

        $this->success('', [
            'list'   => $list,
            'remark' => get_route_remark(),
        ]);
    }
}