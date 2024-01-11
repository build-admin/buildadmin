<?php

namespace app\admin\controller\examples\table;

use app\common\controller\Backend;

/**
 * 编程式刷新表格
 */
class Refresh extends Backend
{
    /**
     * Refresh模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\Refresh
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\examples\table\Refresh;
    }

    public function index(): void
    {
        $sleep = $this->request->get('sleep/b', false);
        if ($sleep) sleep(3);

        parent::index();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}