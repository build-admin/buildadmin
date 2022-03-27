<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\UserMoneyLog;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use app\admin\model\User;
use think\facade\Db;
use Exception;

class MoneyLog extends Backend
{
    protected $model = null;

    protected $withJoinTable = ['user'];

    // 排除字段
    protected $preExcludeFields = ['createtime'];

    protected $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserMoneyLog();
    }

    /**
     * 添加
     */
    public function add($userId = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $user = User::where('id', (int)$userId)->find();
        if (!$user) {
            $this->error('用户找不到啦~');
        }
        $this->success('', [
            'user' => $user
        ]);
    }
}