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
        if (in_array($row['type'], $this->jsonDecodeType)) {
            $arr = json_decode($value, true);
            return $arr ? $arr : [];
        } else {
            return $value ? $value : '';
        }
    }
}