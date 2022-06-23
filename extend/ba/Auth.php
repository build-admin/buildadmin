<?php

namespace ba;

use think\facade\Db;

/**
 * 权限规则检测类
 */
class Auth
{
    /**
     * @var Auth 对象实例
     */
    protected static $instance;

    /**
     * 用户有权限的规则节点
     */
    protected $rules = [];

    /**
     * 默认配置
     * @var array|string[]
     */
    protected $config = [
        'auth_group'        => 'admin_group', // 用户组数据表名
        'auth_group_access' => 'admin_group_access', // 用户-用户组关系表
        'auth_rule'         => 'menu_rule', // 权限规则表
    ];

    /**
     * 子菜单规则数组
     * @var array
     */
    protected $childrens = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param $name
     * @return mixed|string
     */
    public function __get($name)
    {
        return $this->config[$name];
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 获取菜单规则列表
     * @access public
     * @param int $uid 用户ID
     * @return array
     */
    public function getMenus($uid)
    {
        if (!$this->rules) {
            $this->getRuleList($uid);
        }
        if (!$this->rules) {
            return [];
        }
        foreach ($this->rules as $rule) {
            $this->childrens[$rule['pid']][] = $rule;
        }
        if (!isset($this->childrens[0])) {
            return [];
        }

        return $this->getChildren($this->childrens[0]);
    }

    /**
     * 获取数组中所有菜单规则的子规则
     * @param array $rules 菜单规则
     */
    public function getChildren($rules): array
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($rule['id'], $this->childrens)) {
                $rules[$key]['children'] = $this->getChildren($this->childrens[$rule['id']]);
            }
        }
        return $rules;
    }

    /**
     * 检查是否有某权限
     * @param string $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int    $uid      用户ID
     * @param string $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string $mode     如果不使用 url 则菜单规则name匹配到即通过
     * @return bool
     */
    public function check($name, $uid, $relation = 'or', $mode = 'url')
    {
        // 获取用户需要验证的所有有效规则列表
        $rulelist = $this->getRuleList($uid);
        if (in_array('*', $rulelist)) {
            return true;
        }

        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize(request()->param())));
        }
        foreach ($rulelist as $rule) {
            $query = preg_replace('/^.+\?/U', '', $rule);
            if ('url' == $mode && $query != $rule) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $rule      = preg_replace('/\?.*$/U', '', $rule);
                if (in_array($rule, $name) && $intersect == $param) {
                    // 如果节点相符且url参数满足
                    $list[] = $rule;
                }
            } else {
                if (in_array($rule, $name)) {
                    $list[] = $rule;
                }
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 获得权限规则列表
     * @param int $uid 用户id
     * @return array
     */
    public function getRuleList($uid)
    {
        // 静态保存所有用户验证通过的权限列表
        static $ruleList = [];
        if (isset($ruleList[$uid])) {
            return $ruleList[$uid];
        }

        // 读取用户规则节点
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) {
            $ruleList[$uid] = [];
            return [];
        }

        $where[] = ['status', '=', '1'];
        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }
        // 读取用户组所有权限规则
        $this->rules = Db::name($this->config['auth_rule'])
            ->withoutField(['remark', 'status', 'weigh', 'updatetime', 'createtime'])
            ->where($where)
            ->order('weigh desc,id asc')
            ->select()->toArray();

        // 用户规则
        $rules = [];
        if (in_array('*', $ids)) {
            $rules[] = "*";
        }
        foreach ($this->rules as $key => $rule) {
            $rules[$rule['id']] = strtolower($rule['name']);
            if (isset($rule['keepalive']) && $rule['keepalive']) {
                $this->rules[$key]['keepAlive'] = $rule['name'];
            }
            unset($this->rules[$key]['keepalive']);
        }
        $ruleList[$uid] = $rules;
        return array_unique($rules);
    }

    /**
     * 获取权限规则ids
     * @param $uid
     * @return array
     */
    public function getRuleIds($uid)
    {
        // 用户的组别和规则ID
        $groups = $this->getGroups($uid);
        $ids    = [];
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        return $ids;
    }

    /**
     * 获取用户所有分组和对应权限规则
     * @param $uid
     * @return array
     */
    public function getGroups($uid)
    {
        static $groups = [];
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }

        if ($this->config['auth_group_access']) {
            $userGroups = Db::name($this->config['auth_group_access'])
                ->alias('aga')
                ->join($this->config['auth_group'] . ' ag', 'aga.group_id = ag.id', 'LEFT')
                ->field('aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules')
                ->where("aga.uid='{$uid}' and ag.status='1'")
                ->select();
        } else {
            $userGroups = Db::name('user')
                ->alias('u')
                ->join($this->config['auth_group'] . ' ag', 'u.group_id = ag.id', 'LEFT')
                ->field('u.id as uid,u.group_id,ag.id,ag.name,ag.rules')
                ->where("u.id='{$uid}' and ag.status='1'")
                ->select();
        }

        $groups[$uid] = $userGroups ?: [];
        return $groups[$uid];
    }
}