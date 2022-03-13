<?php

namespace app\common\controller;

use app\admin\library\Auth;
use think\db\exception\PDOException;
use think\facade\Cookie;
use think\facade\Db;

class Backend extends Api
{
    protected $noNeedLogin      = [];
    protected $noNeedPermission = [];
    protected $preExcludeFields = [];

    /**
     * 权限类实例
     * @var Auth
     */
    protected $auth = null;

    protected $model = null;

    /**
     * 权重(排序)字段
     */
    protected $weighField = 'weigh';

    /**
     * 表格拖拽排序时,两个权重相等则自动重新整理
     * config/buildadmin.php文件中的auto_sort_eq_weight为默认值
     * null=取默认值,false=关,true=开
     */
    protected $autoSortEqWeight = null;

    /**
     * 快速搜索字段
     */
    protected $quickSearchField = 'id';

    /**
     * 是否开启模型验证
     */
    protected $modelValidate = false;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * 引入traits
     * traits内实现了index、add、edit等方法
     */
    use \app\admin\library\traits\Backend;

    public function initialize()
    {
        parent::initialize();

        // 检测数据库连接
        try {
            Db::execute("SELECT 1");
        } catch (PDOException $e) {
            $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
        }

        $this->auth = Auth::instance();
        $routePath  = $this->controllerPath . '/' . $this->request->action(true);
        $token      = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', Cookie::get('batoken') ?: false));
        if (!$this->auth->actionInArr($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'routeName' => 'adminLogin'
                ], 302);
            }
            if (!$this->auth->actionInArr($this->noNeedPermission)) {
                if (!$this->auth->check($routePath)) {
                    $this->error(__('You have no permission'), [
                        'routeName' => 'admin'
                    ], 302);
                }
            }
        } else {
            if ($token) {
                $this->auth->init($token);
            }
        }
    }

    public function queryBuilder()
    {

    }
}