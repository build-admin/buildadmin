<?php

namespace app\common\controller;

use app\common\controller\Api;
use app\admin\library\Auth;
use think\facade\Cookie;

class Backend extends Api
{
    protected $noNeedLogin      = [];
    protected $noNeedPermission = [];

    /**
     * 权限类实例
     * @var Auth
     */
    protected $auth = null;

    protected $model = null;

    protected $quickSearchField = 'id';

    public function _initialize()
    {
        $modulename     = app('http')->getName();
        $controllername = $this->request->controller(true);
        $actionname     = $this->request->action(true);

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        $this->auth = Auth::instance();
        $token      = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', Cookie::get('batoken') ?: false));
        if (!$this->auth->actionInArr($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'routeName' => 'adminLogin'
                ], 302);
            }
            if (!$this->auth->actionInArr($this->noNeedPermission)) {
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'), [
                        'routeName' => 'adminLogin'
                    ], 302);
                }
            }
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