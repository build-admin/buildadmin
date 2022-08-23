<?php

namespace app\admin\controller\security;

use app\common\controller\Backend;
use app\admin\model\DataRecycleLog as DataRecycleLogModel;
use think\db\exception\PDOException;
use Exception;
use think\facade\Db;

class DataRecycleLog extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = [];

    protected $quickSearchField = 'recycle.name';

    protected $withJoinTable = ['recycle', 'admin'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new DataRecycleLogModel();
    }

    public function restore($ids = null)
    {
        $data = $this->model->where('id', 'in', $ids)->select();
        if (!$data) {
            $this->error(__('Record not found'));
        }

        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $row) {
                $recycleData = json_decode($row->data, true);
                if (is_array($recycleData) && Db::name($row->data_table)->insert($recycleData)) {
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
            $this->error(__('No rows were restore'));
        }
    }

    public function info($id = null)
    {
        $row = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->where('data_recycle_log.id', $id)
            ->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }
        $data = $this->jsonToArray($row->data);
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->jsonToArray($item);
            }
        }
        $row->data = $data;

        $this->success('', [
            'row' => $row
        ]);
    }

    protected function jsonToArray($value = '')
    {
        if (!is_string($value)) {
            return $value;
        }
        $data = json_decode($value, true);
        if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
            return $data;
        }
        return $value;
    }
}