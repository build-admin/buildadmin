<?php

namespace app\admin\controller\examples\table\form;

use app\common\controller\Backend;

/**
 * 数据编辑之前预处理
 */
class Edit extends Backend
{
    /**
     * Edit模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\form\Edit
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\examples\table\form\Edit;
    }
}