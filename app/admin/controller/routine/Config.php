<?php

namespace app\admin\controller\routine;

use app\common\controller\Backend;
use app\admin\model\Config as ConfigModel;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;
use Exception;

class Config extends Backend
{
    protected $model = null;

    protected $noNeedLogin = ['index'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index()
    {
        $configGroup = get_sys_config('config_group');
        $config      = $this->model->order('weigh desc')->select()->toArray();
        $list        = [];
        foreach ($config as $item) {
            $item['title']                  = __($item['title']);
            $list[$item['group']]['list'][] = $item;
        }
        foreach ($configGroup as $key => $item) {
            $list[$item['key']]['name']  = $item['key'];
            $list[$item['key']]['title'] = __($item['value']);

            $newConfigGroup[$item['key']] = $item['value'];
        }

        $this->success('', [
            'list'        => $list,
            'remark'      => get_route_remark(),
            'configGroup' => $newConfigGroup
        ]);
    }

    /**
     * 编辑
     * @param null $id
     */
    public function edit($id = null)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);

            $configValue = [];
            $all         = $this->model->select();
            foreach ($all as $item) {
                if (array_key_exists($item->name, $data)) {
                    $configValue[] = [
                        'id'    => $item->id,
                        'type' => $item->getData('type'),
                        'value' => $data[$item->name]
                    ];
                }
            }

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $this->model->saveAll($configValue);
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
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }

        }
    }
}