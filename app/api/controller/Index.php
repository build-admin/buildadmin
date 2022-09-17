<?php

namespace app\api\controller;

use think\facade\Config;
use app\common\controller\Frontend;

class Index extends Frontend
{
    protected $noNeedLogin = ['index'];

    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->success('', [
            'site'               => [
                'site_name'     => get_sys_config('site_name'),
                'record_number' => get_sys_config('record_number'),
                'version'       => get_sys_config('version'),
                'cdn_url'       => full_url(),
                'upload'        => get_upload_config(),
            ],
            'open_member_center' => Config::get('buildadmin.open_member_center'),
        ]);
    }
}