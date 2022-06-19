<?php

namespace app\common\controller;

class Frontend extends Api
{
    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需管理员登录
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     */
    protected $noNeedPermission = [];

    /**
     * 权限类实例
     * @var
     */
    protected $auth = null;

    public function initialize()
    {
        parent::initialize();
    }
}