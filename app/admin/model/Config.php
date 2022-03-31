<?php

namespace app\admin\model;

use think\Model;

class Config extends Model
{
    protected $append = [
        'value',
    ];

    protected $jsonDecodeType = ['checkbox', 'array'];

    public function getValueAttr($value, $row)
    {
        if ($value && in_array($row['type'], $this->jsonDecodeType)) {
            return json_decode($value, true);
        }

        return $value;
    }
}