<?php

namespace ba;

/**
 *
 */
class Tree
{
    /**
     * @var Tree
     */
    protected static $instance;

    /**
     * 生成树型结构所需修饰符号
     * @var array
     */
    public static $icon = array('│', '├', '└');


    /**
     * 初始化
     * @access public
     * @return Tree
     */
    public static function instance(): Tree
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 将数组渲染为树状,需自备children
     * @param array  $arr         要改为树状的数组
     * @param string $field       '树枝'字段
     * @param int    $level       递归数组层次,无需手动维护
     * @param false  $superiorEnd 递归上一级树枝是否结束,无需手动维护
     * @return array
     */
    public static function getTreeArray($arr, $field = 'name', $level = 0, $superiorEnd = false): array
    {
        if (!is_array($arr)) {
            return [];
        }
        $level++;
        $number = 1;
        $total  = count($arr);
        foreach ($arr as $key => $item) {
            $prefix = ($number == $total) ? self::$icon[2] : self::$icon[1];
            if ($level == 2) {
                $arr[$key][$field] = str_pad('', 4, ' ') . $prefix . $item[$field];
            } else if ($level >= 3) {
                $arr[$key][$field] = str_pad('', 4, ' ') . ($superiorEnd ? '' : self::$icon[0]) . str_pad('', ($level - 2) * 4, ' ') . $prefix . $item[$field];
            }

            if ($item['children']) {
                $arr[$key]['children'] = self::getTreeArray($item['children'], $field, $level, $number == $total);
            }
            $number++;
        }
        return $arr;
    }

    /**
     * 递归合并树状数组,多维变二维
     * @param array $data 要合并的数组
     * @return array
     */
    public static function assembleTree($data)
    {
        $arr = [];
        foreach ($data as $v) {
            $children = $v['children'] ?? [];
            unset($v['children']);
            $arr[] = $v;
            if ($children) {
                $arr = array_merge($arr, self::assembleTree($children));
            }
        }
        return $arr;
    }
}