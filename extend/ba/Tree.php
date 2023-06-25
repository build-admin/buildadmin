<?php

namespace ba;

/**
 * 树
 */
class Tree
{
    /**
     * 实例
     * @var ?Tree
     */
    protected static ?Tree $instance = null;

    /**
     * 生成树型结构所需修饰符号
     * @var array
     */
    public static array $icon = array('│', '├', '└');

    /**
     * 子级数据（树枝）
     * @var array
     */
    protected array $children = [];


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
     * 将数组某个字段渲染为树状,需自备children children可通过$this->assembleChild()方法组装
     * @param array  $arr         要改为树状的数组
     * @param string $field       '树枝'字段
     * @param int    $level       递归数组层次,无需手动维护
     * @param bool   $superiorEnd 递归上一级树枝是否结束,无需手动维护
     * @return array
     */
    public static function getTreeArray(array $arr, string $field = 'name', int $level = 0, bool $superiorEnd = false): array
    {
        $level++;
        $number = 1;
        $total  = count($arr);
        foreach ($arr as $key => $item) {
            $prefix = ($number == $total) ? self::$icon[2] : self::$icon[1];
            if ($level == 2) {
                $arr[$key][$field] = str_pad('', 4) . $prefix . $item[$field];
            } elseif ($level >= 3) {
                $arr[$key][$field] = str_pad('', 4) . ($superiorEnd ? '' : self::$icon[0]) . str_pad('', ($level - 2) * 4) . $prefix . $item[$field];
            }

            if (isset($item['children']) && $item['children']) {
                $arr[$key]['children'] = self::getTreeArray($item['children'], $field, $level, $number == $total);
            }
            $number++;
        }
        return $arr;
    }

    /**
     * 递归合并树状数组（根据children多维变二维方便渲染）
     * @param array $data 要合并的数组 ['id' => 1, 'pid' => 0, 'title' => '标题1', 'children' => ['id' => 2, 'pid' => 1, 'title' => '    └标题1-1']]
     * @return array [['id' => 1, 'pid' => 0, 'title' => '标题1'], ['id' => 2, 'pid' => 1, 'title' => '    └标题1-1']]
     */
    public static function assembleTree(array $data): array
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

    /**
     * 递归的根据指定字段组装 children 数组
     * @param array  $data 数据源 例如：[['id' => 1, 'pid' => 0, title => '标题1'], ['id' => 2, 'pid' => 1, title => '标题1-1']]
     * @param string $pid  存储上级id的字段
     * @param string $pk   主键字段
     * @return array ['id' => 1, 'pid' => 0, 'title' => '标题1', 'children' => ['id' => 2, 'pid' => 1, 'title' => '标题1-1']]
     */
    public function assembleChild(array $data, string $pid = 'pid', string $pk = 'id'): array
    {
        if (!$data) return [];

        $pks            = [];
        $topLevelData   = []; // 顶级数据
        $this->children = []; // 置空子级数据
        foreach ($data as $item) {
            $pks[] = $item[$pk];

            // 以pid组成children
            $this->children[$item[$pid]][] = $item;
        }
        // 上级不存在的就是顶级，只获取它们的 children
        foreach ($data as $item) {
            if (!in_array($item[$pid], $pks)) {
                $topLevelData[] = $item;
            }
        }

        if (count($this->children) > 0) {
            foreach ($topLevelData as $key => $item) {
                $topLevelData[$key]['children'] = $this->getChildren($this->children[$item[$pk]] ?? [], $pk);
            }
            return $topLevelData;
        } else {
            return $data;
        }
    }

    /**
     * 获取 children 数组
     * 辅助 assembleChild 组装 children
     * @param array  $data
     * @param string $pk
     * @return array
     */
    protected function getChildren(array $data, string $pk = 'id'): array
    {
        if (!$data) return [];
        foreach ($data as $key => $item) {
            if (array_key_exists($item[$pk], $this->children)) {
                $data[$key]['children'] = $this->getChildren($this->children[$item[$pk]], $pk);
            }
        }
        return $data;
    }
}