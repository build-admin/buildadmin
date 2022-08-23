<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\SensitiveDataLog as SensitiveDataLogModel;
use think\db\exception\PDOException;
use think\facade\Db;
use Exception;

class SensitiveDataLog extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = [];

    protected $quickSearchField = 'sensitive.name';

    protected $withJoinTable = ['sensitive', 'admin'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new SensitiveDataLogModel();
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
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

    public function info($id = null)
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

    public function rollback($ids = null)
    {
        $data = $this->model->where('id', 'in', $ids)->select();
        if (!$data) {
            $this->error(__('Record not found'));
        }

        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $row) {
                if (Db::name($row->data_table)->where($row->primary_key, $row->id_value)->update([
                    $row->data_field => $row->before
                ])) {
                    $row->delete();
                    $count++;
                }
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were rollback'));
        }
    }
}