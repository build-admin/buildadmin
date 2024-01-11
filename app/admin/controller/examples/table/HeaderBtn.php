<?php

namespace app\admin\controller\examples\table;

use app\common\controller\Backend;

/**
 * 自定义表头按钮
 */
class HeaderBtn extends Backend
{
    /**
     * HeaderBtn模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\HeaderBtn
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\examples\table\HeaderBtn;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}