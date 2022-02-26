<?php

namespace app\common\controller;

use app\common\controller\Api;
use app\admin\library\Auth;
use think\facade\Cookie;

class Backend extends Api
{
    protected $noNeedLogin      = [];
    protected $noNeedPermission = [];

    protected $auth = null;

    protected $model = null;

    protected $quickSearchField = 'id';

    public function _initialize()
    {
        $this->auth = Auth::instance();
        $token      = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token') ?: false));
        if (!$this->auth->needLogin($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'url' => '/admin'
                ]);
            }

            // 权限检查 - 待完善
        } else {
            if ($token) {
                $this->auth->init($token);
            }
        }

        // 语言检测
        // 加载语言包？
        // 初始化需要用到的配置
    }
}