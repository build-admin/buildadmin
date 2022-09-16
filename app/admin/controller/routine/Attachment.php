<?php

namespace app\admin\controller\routine;

use Exception;
use app\common\controller\Backend;
use app\common\model\Attachment as AttachmentModel;
use think\facade\Db;
use think\db\exception\PDOException;

class Attachment extends Backend
{
    protected $model = null;

    protected $quickSearchField = 'name';

    protected $withJoinTable = ['admin', 'user'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new AttachmentModel();
    }

    /**
     * 删除
     * @param array $ids
     */
    public function del(array $ids = [])
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        try {
            foreach ($data as $v) {
                $filePath = path_transform(public_path() . ltrim($v->url, '/'));
                if (file_exists($filePath)) {
                    unlink($filePath);
                    del_empty_dir(dirname($filePath));
                }
                $count += $v->delete();
            }
        } catch (PDOException|Exception $e) {
            $this->error(__('%d records and files have been deleted', [$count]) . $e->getMessage());
        }
        if ($count) {
            $this->success(__('%d records and files have been deleted', [$count]));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}