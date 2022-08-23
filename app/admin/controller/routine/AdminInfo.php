<?php

namespace app\admin\controller\routine;

use Exception;
use app\common\controller\Backend;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;

class AdminInfo extends Backend
{
    protected $model = null;

    // 排除字段
    protected $preExcludeFields = ['username', 'lastlogintime', 'password', 'salt', 'status'];
    // 输出字段
    protected $authAllowFields = ['id', 'username', 'nickname', 'avatar', 'email', 'mobile', 'motto', 'lastlogintime'];

    public function initialize()
    {
        parent::initialize();
        $this->auth->setAllowFields($this->authAllowFields);
        $this->model = $this->auth->getAdmin();
    }

    public function index()
    {
        $info = $this->auth->getInfo();
        $this->success('', [
            'info' => $info
        ]);
    }

    public function edit($id = null)
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
                } catch (ValidateException $e) {
                    $this->error($e->getMessage());
                }
            }

            if (isset($data['password']) && $data['password']) {
                $this->model->resetPassword($this->auth->id, $data['password']);
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                $result = $row->save($data);
                Db::commit();
            } catch (PDOException|Exception $e) {
                Db::rollback();
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