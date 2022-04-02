<?php

namespace app\admin\model;

use think\Model;

class Config extends Model
{
    protected $append = [
        'value',
        'content',
        'extend',
    ];

    protected $jsonDecodeType = ['checkbox', 'array', 'selects'];
    protected $needContent    = ['radio', 'checkbox', 'select', 'selects'];

    public static function onBeforeInsert($model)
    {
        if (!in_array($model->getData('type'), $model->needContent)) {
            $model->content = null;
        } else {
            $model->content = json_encode($model->handleTextAreaData($model->getData('content')));
        }
        if (is_array($model->rule)) {
            $model->rule = implode(',', $model->rule);
        }
        if ($model->getData('extend')) {
            $model->extend = json_encode($model->handleTextAreaData($model->getData('extend')));
        }
        $model->allow_del = 1;
    }

    public function handleTextAreaData($data)
    {
        $content     = explode("\n", trim($data));
        $contentTemp = [];
        foreach ($content as $item) {
            $item = explode('=', $item);
            if (isset($item[0]) && isset($item[1])) {
                $contentTemp[$item[0]] = $item[1];
            }
        }
        return $contentTemp;
    }

    public function getValueAttr($value, $row)
    {
        if (in_array($row['type'], $this->jsonDecodeType)) {
            $arr = json_decode($value, true);
            return $arr ? $arr : [];
        } else if ($row['type'] == 'switch') {
            return (bool)$value;
        } else {
            return $value ? $value : '';
        }
    }

    public function setValueAttr($value, $row)
    {
        if (in_array($row['type'], $this->jsonDecodeType)) {
            return $value ? json_encode($value) : '';
        } else if ($row['type'] == 'switch') {
            return $value ? '1' : '0';
        }

        return $value;
    }

    public function getContentAttr($value, $row)
    {
        if (in_array($row['type'], $this->needContent)) {
            $arr = json_decode($value, true);
            return $arr ? $arr : [];
        } else {
            return '';
        }
    }

    public function getExtendAttr($value, $row)
    {
        if ($value) {
            $arr = json_decode($value, true);
            return $arr ? $arr : [];
        }

        return [];
    }
}