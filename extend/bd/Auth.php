<?php

namespace bd;

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
     * 默认配置
     * @var array|string[]
     */
    protected $config = [
        'auth_group'        => 'admin_group', // 用户组数据表名
        'auth_group_access' => 'admin_group_access', // 用户-用户组关系表
        'auth_rule'         => 'menu_rule', // 权限规则表
    ];

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
        //读取用户组所有权限规则
        $rulesData = Db::name($this->auth_rule)->where($where)->field('id,pid,type,title,name,path,icon,menu_type,url,component')->select();

        // 用户规则
        $rules = [];
        if (in_array('*', $ids)) {
            $rules[] = "*";
        }
        foreach ($rulesData as $rule) {
            $rules[$rule['id']] = strtolower($rule['name']);
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

        $userGroups = Db::name($this->auth_group_access)
            ->alias('aga')
            ->join($this->auth_group . ' ag', 'aga.group_id = ag.id', 'LEFT')
            ->field('aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules')
            ->where("aga.uid='{$uid}' and ag.status='1'")
            ->select();

        $groups[$uid] = $userGroups ?: [];
        return $groups[$uid];
    }
}