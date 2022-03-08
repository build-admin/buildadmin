<?php

namespace app\admin\library\traits;

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
        $this->success('edit', [
            'row' => $row
        ]);
    }
}