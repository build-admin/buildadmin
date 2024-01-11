<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * Tree
 */
class Tree extends Model
{
    // 表名
    protected $name = 'examples_table_tree';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    protected $append = [
        'children'
    ];

    public function getChildrenAttr()
    {
        return [
            [
                'id'          => 'none',
                'string'      => 'string',
                'date'        => '2023-08-09',
                'address'     => '详细地址',
                'code'        => '500234',
                'create_time' => '1691560192',
            ],
            [
                'id'          => 'none',
                'string'      => 'string1',
                'date'        => '2023-08-10',
                'address'     => '详细地址1',
                'code'        => '500234',
                'create_time' => '1691560192',
            ]
        ];
    }

}