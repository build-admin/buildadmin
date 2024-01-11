<?php

namespace app\admin\controller\examples\table\cell;

use app\common\controller\Backend;

/**
 * 单元格slot渲染
 */
class Slot extends Backend
{
    /**
     * Slot模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\cell\Slot
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\examples\table\cell\Slot;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}