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
        $config      = $this->model->order('weigh desc')->select()->toArray();
        $list        = [];
        foreach ($config as $item) {
            $item['title']                  = __($item['title']);
            $list[$item['group']]['list'][] = $item;
        }
        foreach ($configGroup as $key => $item) {
            $list[$item['key']]['name']  = $item['key'];
            $list[$item['key']]['title'] = __($item['value']);

            $newConfigGroup[$item['key']] = $item['value'];
        }

        $this->success('', [
            'list'        => $list,
            'remark'      => get_route_remark(),
            'configGroup' => $newConfigGroup
        ]);
    }
}