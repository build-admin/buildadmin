<?php

namespace app\admin\controller\routine;

use Throwable;
use app\admin\model\Admin;
use app\common\controller\Backend;

class AdminInfo extends Backend
{
    /**
     * @var object
     * @phpstan-var Admin
     */
    protected object $model;

    protected string|array $preExcludeFields = ['username', 'last_login_time', 'password', 'salt', 'status'];

    protected array $authAllowFields = ['id', 'username', 'nickname', 'avatar', 'email', 'mobile', 'motto', 'last_login_time'];

    public function initialize(): void
    {
        parent::initialize();
        $this->auth->setAllowFields($this->authAllowFields);
        $this->model = $this->auth->getAdmin();
    }

    public function index(): void
    {
        $info = $this->auth->getInfo();
        $this->success('', [
            'info' => $info
        ]);
    }

    public function edit($id = null): void
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            if (isset($data['avatar']) && $data['avatar']) {
                $row->avatar = $data['avatar'];
                if ($row->save()) {
                    $this->success(__('Avatar modified successfully!'));
                }
            }

            // 数据验证
            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate;
                    $validate->scene('info')->check($data);
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            if (isset($data['password']) && $data['password']) {
                $this->model->resetPassword($this->auth->id, $data['password']);
            }

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }
    }
}