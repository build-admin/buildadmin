<?php

namespace app\admin\controller\examples\table\form;

use app\common\controller\Backend;

/**
 * 表单提交前数据处理
 */
class Submit extends Backend
{
    /**
     * Submit模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\form\Submit
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\examples\table\form\Submit;
    }
}