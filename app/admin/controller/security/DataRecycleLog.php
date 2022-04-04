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
                if (is_array($recycleData) && Db::table($row->data_table)->insert($recycleData)) {
                    $row->delete();
                    $count++;
                }
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were restore'));
        }
    }
}