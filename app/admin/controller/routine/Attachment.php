<?php

namespace app\admin\controller\routine;

use Throwable;
use ba\Filesystem;
use think\facade\Event;
use app\common\controller\Backend;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends Backend
{
    /**
     * @var object
     * @phpstan-var AttachmentModel
     */
    protected object $model;

    protected string|array $quickSearchField = 'name';

    protected array $withJoinTable = ['admin', 'user'];

    protected string|array $defaultSortField = 'last_upload_time,desc';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AttachmentModel();
    }

    /**
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): void
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
                Event::trigger('AttachmentDel', $v);
                $filePath = Filesystem::fsFit(public_path() . ltrim($v->url, '/'));
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Filesystem::delEmptyDir(dirname($filePath));
                }
                $count += $v->delete();
            }
        } catch (Throwable $e) {
            $this->error(__('%d records and files have been deleted', [$count]) . $e->getMessage());
        }
        if ($count) {
            $this->success(__('%d records and files have been deleted', [$count]));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}