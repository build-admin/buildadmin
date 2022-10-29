<?php

namespace app\common\controller;

use app\admin\library\Auth;
use think\db\exception\PDOException;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Event;

class Backend extends Api
{
    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需管理员登录
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     */
    protected $noNeedPermission = [];

    /**
     * 新增/编辑时，对前端发送的字段进行排除（忽略不入库）
     */
    protected $preExcludeFields = [];

    /**
     * 权限类实例
     * @var Auth
     */
    protected $auth = null;

    protected $model = null;

    /**
     * 权重字段
     */
    protected $weighField = 'weigh';

    /**
     * 默认排序
     */
    protected $defaultSortField = 'id,desc';

    /**
     * 表格拖拽排序时,两个权重相等则自动重新整理
     * config/buildadmin.php文件中的auto_sort_eq_weight为默认值
     * null=取默认值,false=关,true=开
     */
    protected $autoSortEqWeight = null;

    /**
     * 快速搜索字段
     */
    protected $quickSearchField = 'id';

    /**
     * 是否开启模型验证
     */
    protected $modelValidate = true;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * 关联查询方法名
     * 方法应定义在模型中
     */
    protected $withJoinTable = [];

    /**
     * 关联查询JOIN方式
     */
    protected $withJoinType = 'LEFT';

    /**
     * 开启数据限制
     * false=关闭
     * personal=仅限个人
     * allAuth=拥有某管理员所有的权限时
     * allAuthAndOthers=拥有某管理员所有的权限并且还有其他权限时
     * parent=上级分组中的管理员可查
     * 指定分组中的管理员可查，比如 $dataLimit = 2;
     * 启用请确保数据表内存在 admin_id 字段，可以查询/编辑数据的管理员为admin_id对应的管理员+数据限制所表示的管理员们
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充字段值为当前管理员id
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 查看请求返回的主表字段控制
     */
    protected $indexField = ['*'];

    /**
     * 引入traits
     * traits内实现了index、add、edit等方法
     */
    use \app\admin\library\traits\Backend;

    public function initialize()
    {
        parent::initialize();

        // 检测数据库连接
        try {
            Db::execute("SELECT 1");
        } catch (PDOException $e) {
            $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
        }

        $this->auth = Auth::instance();
        $routePath  = $this->app->request->controllerPath . '/' . $this->request->action(true);
        $token      = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', Cookie::get('batoken') ?: false));
        if (!action_in_arr($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'routePath' => '/admin/login'
                ], 302);
            }
            if (!action_in_arr($this->noNeedPermission)) {
                if (!$this->auth->check($routePath)) {
                    $this->error(__('You have no permission'), [
                        'routePath' => '/admin'
                    ], 302);
                }
            }
        } else {
            if ($token) {
                $this->auth->init($token);
            }
        }

        // 管理员验权和登录标签位
        Event::trigger('backendInit', $this->auth);
    }

    public function queryBuilder(): array
    {
        if (empty($this->model)) {
            return [];
        }
        $pk          = $this->model->getPk();
        $quickSearch = $this->request->get("quick_search/s", '');
        $limit       = $this->request->get("limit/d", 10);
        $order       = $this->request->get("order/s", '');
        $search      = $this->request->get("search/a", []);
        $initKey     = $this->request->get("initKey/s", $pk);
        $initValue   = $this->request->get("initValue/a", '');

        $where              = [];
        $modelTable         = strtolower($this->model->getTable());
        $alias[$modelTable] = parse_name(basename(str_replace('\\', '/', get_class($this->model))));
        $tableAlias         = $alias[$modelTable] . '.';

        // 快速搜索
        if ($quickSearch) {
            $quickSearchArr = is_array($this->quickSearchField) ? $this->quickSearchField : explode(',', $this->quickSearchField);
            foreach ($quickSearchArr as $k => $v) {
                $quickSearchArr[$k] = stripos($v, ".") === false ? $tableAlias . $v : $v;
            }
            $where[] = [implode("|", $quickSearchArr), "LIKE", "%{$quickSearch}%"];
        }
        if ($initValue) {
            $where[] = [$initKey, 'in', $initValue];
            $limit   = 999999;
        }

        // 排序
        if ($order) {
            $order = explode(',', $order);
            if (isset($order[0]) && isset($order[1]) && ($order[1] == 'asc' || $order[1] == 'desc')) {
                $order = [$order[0] => $order[1]];
            }
        } else {
            if (is_array($this->defaultSortField)) {
                $order = $this->defaultSortField;
            } else {
                $order = explode(',', $this->defaultSortField);
                if (isset($order[0]) && isset($order[1])) {
                    $order = [$order[0] => $order[1]];
                } else {
                    $order = [$pk => 'desc'];
                }
            }
        }

        // 通用搜索组装
        foreach ($search as $item) {
            $field = json_decode($item, true);
            if (!is_array($field) || !isset($field['operator']) || !isset($field['field'])) {
                continue;
            }

            if (stripos($field['field'], '.') !== false) {
                $fieldArr            = explode('.', $field['field']);
                $alias[$fieldArr[0]] = $fieldArr[0];
                $fieldName           = $field['field'];
            } else {
                $fieldName = $tableAlias . $field['field'];
            }

            // 日期时间
            if (isset($field['render']) && $field['render'] == 'datetime') {
                if ($field['operator'] == 'RANGE') {
                    $datetimeArr = explode(',', $field['val']);
                    if (!isset($datetimeArr[1])) {
                        continue;
                    }
                    $datetimeArr = array_filter(array_map("strtotime", $datetimeArr));
                    $where[]     = [$fieldName, str_replace('RANGE', 'BETWEEN', $field['operator']), $datetimeArr];
                    continue;
                }
                $where[] = [$fieldName, '=', strtotime($field['val'])];
                continue;
            }

            // 范围查询
            if ($field['operator'] == 'RANGE' || $field['operator'] == 'NOT RANGE') {
                if (stripos($field['val'], ',') === false) {
                    continue;
                }
                $arr = explode(',', $field['val']);
                // 重新确定操作符
                if (!isset($arr[0]) || $arr[0] === '') {
                    $operator = $field['operator'] == 'RANGE' ? '<=' : '>';
                    $arr      = $arr[1];
                } elseif (!isset($arr[1]) || $arr[1] === '') {
                    $operator = $field['operator'] == 'RANGE' ? '>=' : '<';
                    $arr      = $arr[0];
                } else {
                    $operator = str_replace('RANGE', 'BETWEEN', $field['operator']);
                }
                $where[] = [$fieldName, $operator, $arr];
                continue;
            }

            switch ($field['operator']) {
                case '=':
                case '<>':
                    $where[] = [$fieldName, $field['operator'], (string)$field['val']];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                    $where[] = [$fieldName, $field['operator'], "%{$field['val']}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$fieldName, $field['operator'], intval($field['val'])];
                    break;
                case 'FIND_IN_SET':
                    $where[] = [$fieldName, 'find in set', $field['val']];
                    break;
                case 'IN':
                case 'NOT IN':
                    $where[] = [$fieldName, $field['operator'], is_array($field['val']) ? $field['val'] : explode(',', $field['val'])];
                    break;
                case 'NULL':
                case 'NOT NULL':
                    $where[] = [$fieldName, strtolower($field['operator'])];
                    break;
            }
        }

        // 数据权限
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$tableAlias . $this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        return [$where, $alias, $limit, $order];
    }

    protected function getDataLimitAdminIds(): array
    {
        if (!$this->dataLimit || $this->auth->isSuperAdmin()) {
            return [];
        }
        $adminIds = [];
        if ($this->dataLimit == 'parent') {
            // 取得当前管理员的下级分组们
            $parentGroups = $this->auth->getAdminChildGroups();
            if ($parentGroups) {
                // 取得分组内的所有管理员
                $adminIds = $this->auth->getGroupAdmins($parentGroups);
            }
        } elseif (is_numeric($this->dataLimit) && $this->dataLimit > 0) {
            // 在组内，可查看所有，不在组内，可查看自己的
            $adminIds = $this->auth->getGroupAdmins([$this->dataLimit]);
            return in_array($this->auth->id, $adminIds) ? [] : [$this->auth->id];
        } elseif ($this->dataLimit == 'allAuth' || $this->dataLimit == 'allAuthAndOthers') {
            // 取得拥有他所有权限的分组
            $allAuthGroups = $this->auth->getAllAuthGroups($this->dataLimit);
            // 取得分组内的所有管理员
            $adminIds = $this->auth->getGroupAdmins($allAuthGroups);
        }
        $adminIds[] = $this->auth->id;
        return array_unique($adminIds);
    }
}