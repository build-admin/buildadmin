<?php

namespace app\admin\library\traits;

use Exception;
use think\facade\Db;
use think\db\exception\PDOException;
use think\exception\ValidateException;

trait Backend
{
    protected function excludeFields($params)
    {
        if (!is_array($this->preExcludeFields)) {
            $this->preExcludeFields = explode(',', $this->preExcludeFields);
        }

        foreach ($this->preExcludeFields as $field) {
            if (array_key_exists($field, $params)) {
                unset($params[$field]);
            }
        }
        return $params;
    }

    public function index()
    {

    }

    public function edit($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                $result = $row->save($data);
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

        $this->success('edit', [
            'row' => $row
        ]);
    }
}