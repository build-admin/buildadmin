<?php

namespace app\admin\controller\security;

use Throwable;
use think\facade\Db;
use app\common\controller\Backend;
use app\admin\model\SensitiveDataLog as SensitiveDataLogModel;

class SensitiveDataLog extends Backend
{
    /**
     * @var object
     * @phpstan-var SensitiveDataLogModel
     */
    protected object $model;

    // 排除字段
    protected string|array $preExcludeFields = [];

    protected string|array $quickSearchField = 'sensitive.name';

    protected array $withJoinTable = ['sensitive', 'admin'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new SensitiveDataLogModel();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        foreach ($res->items() as $item) {
            $item->id_value = $item['primary_key'] . '=' . $item->id_value;
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 详情
     * @param string|int|null $id
     * @throws Throwable
     */
    public function info(string|int $id = null): void
    {
        $row = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->where('sensitive_data_log.id', $id)
            ->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 回滚
     * @param array|null $ids
     * @throws Throwable
     */
    public function rollback(array $ids = null): void
    {
        $data = $this->model->where('id', 'in', $ids)->select();
        if (!$data) {
            $this->error(__('Record not found'));
        }

        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $row) {
                if (Db::name($row->data_table)->where($row->primary_key, $row->id_value)->update([
                    $row->data_field => $row->before
                ])) {
                    $row->delete();
                    $count++;
                }
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were rollback'));
        }
    }
}