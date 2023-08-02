<?php

namespace app\admin\controller\user;

use Throwable;
use ba\Random;
use app\common\controller\Backend;
use app\admin\model\User as UserModel;

class User extends Backend
{
    /**
     * @var object
     * @phpstan-var UserModel
     */
    protected object $model;

    protected array $withJoinTable = ['group'];

    // 排除字段
    protected string|array $preExcludeFields = ['last_login_time', 'login_failure', 'password', 'salt'];

    protected string|array $quickSearchField = ['username', 'nickname', 'id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new UserModel();
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
            ->withoutField('password,salt')
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $salt   = Random::build('alnum', 16);
            $passwd = encrypt_password($data['password'], $salt);

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                $data['salt']     = $salt;
                $data['password'] = $passwd;
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
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @param string|int|null $id
     * @throws Throwable
     */
    public function edit(string|int $id = null): void
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $password = $this->request->post('password', '');
            if ($password) {
                $this->model->resetPassword($id, $password);
            }
            parent::edit();
        }

        unset($row->salt);
        $row->password = '';
        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 重写select
     * @throws Throwable
     */
    public function select(): void
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        foreach ($res as $re) {
            $re->nickname_text = $re->username . '(ID:' . $re->id . ')';
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}