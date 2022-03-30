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
        if (in_array($row['type'], $this->jsonDecodeType) && $value) {
            return json_decode($value, true);
        }

        return $value;
    }
}