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
     * @var object
     * @phpstan-var CrudLog
     */
    protected object $model;

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id', 'table_name'];

    protected array $noNeedPermission = ['index'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new CrudLog;

        if (!$this->auth->check('crud/crud/index')) {
            $this->error(__('You have no permission'), [], 401);
        }
    }

}