<?php

namespace app\admin\controller\crud;

use app\admin\model\CrudLog;
use app\common\controller\Backend;

/**
 * crud记录
 *
 */
class Log extends Backend
{
    /**
     * Log模型对象
     * @var CrudLog
     */
    protected $model = null;

    protected $preExcludeFields = ['id', 'create_time'];

    protected $quickSearchField = ['id', 'table_name'];

    protected $noNeedPermission = ['index'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new CrudLog;

        if (!$this->auth->check('crud/crud/index')) {
            $this->error(__('You have no permission'), [
                'routePath' => '/admin'
            ], 302);
        }
    }

}