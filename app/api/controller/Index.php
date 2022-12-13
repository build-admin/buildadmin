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
            'site'             => [
                'siteName'     => get_sys_config('site_name'),
                'recordNumber' => get_sys_config('record_number'),
                'version'      => get_sys_config('version'),
                'cdnUrl'       => full_url(),
                'upload'       => get_upload_config(),
            ],
            'openMemberCenter' => Config::get('buildadmin.open_member_center'),
        ]);
    }
}