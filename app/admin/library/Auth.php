<?php

namespace app\admin\library;

use Throwable;
use ba\Random;
use think\facade\Db;
use think\facade\Config;
use app\admin\model\Admin;
use app\common\facade\Token;
use app\admin\model\AdminGroup;

/**
 * 管理员权限类
 * @property int    $id         管理员ID
 * @property string $username   管理员用户名
 * @property string $nickname   管理员昵称
 * @property string $email      管理员邮箱
 * @property string $mobile     管理员手机号
 */
class Auth extends \ba\Auth
{
    /**
     * 需要登录时/无需登录时的响应状态代码
     */
    public const LOGIN_RESPONSE_CODE = 303;

    /**
     * 需要登录标记 - 前台应清理 token、记录当前路由 path、跳转到登录页
     */
    public const NEED_LOGIN = 'need login';

    /**
     * 已经登录标记 - 前台应跳转到基础路由
     */
    public const LOGGED_IN = 'logged in';

    /**
     * 是否登录
     * @var bool
     */
    protected bool $loginEd = false;

    /**
     * 错误消息
     * @var string
     */
    protected string $error = '';

    /**
     * Model实例
     * @var ?Admin
     */
    protected ?Admin $model = null;

    /**
     * 令牌
     * @var string
     */
    protected string $token = '';

    /**
     * 刷新令牌
     * @var string
     */
    protected string $refreshToken = '';

    /**
     * 令牌默认有效期
     * 可在 config/buildadmin.php 内修改默认值
     * @var int
     */
    protected int $keepTime = 86400;

    /**
     * 刷新令牌有效期
     * @var int
     */
    protected int $refreshTokenKeepTime = 2592000;

    /**
     * 允许输出的字段
     * @var array
     */
    protected array $allowFields = ['id', 'username', 'nickname', 'avatar', 'last_login_time'];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->setKeepTime((int)Config::get('buildadmin.admin_token_keep_time'));
    }

    /**
     * 魔术方法-管理员信息字段
     * @param $name
     * @return mixed 字段信息
     */
    public function __get($name): mixed
    {
        return $this->model?->$name;
    }

    /**
     * 初始化
     * @access public
     * @param array $options 传递到 /ba/Auth 的配置信息
     * @return Auth
     */
    public static function instance(array $options = []): Auth
    {
        $request = request();
        if (!isset($request->adminAuth)) {
            $request->adminAuth = new static($options);
        }
        return $request->adminAuth;
    }

    /**
     * 根据Token初始化管理员登录态
     * @param string $token
     * @return bool
     * @throws Throwable
     */
    public function init(string $token): bool
    {
        $tokenData = Token::get($token);
        if ($tokenData) {
            $userId = intval($tokenData['user_id']);
            if ($userId > 0) {
                $this->model = Admin::where('id', $userId)->find();
                if (!$this->model) {
                    $this->setError('Account not exist');
                    return false;
                }
                if ($this->model['status'] != '1') {
                    $this->setError('Account disabled');
                    return false;
                }
                $this->token = $token;
                $this->loginSuccessful();
                return true;
            }
        }
        $this->setError('Token login failed');
        $this->reset();
        return false;
    }

    /**
     * 管理员登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool   $keep     是否保持登录
     * @return bool
     * @throws Throwable
     */
    public function login(string $username, string $password, bool $keep = false): bool
    {
        $this->model = Admin::where('username', $username)->find();
        if (!$this->model) {
            $this->setError('Username is incorrect');
            return false;
        }
        if ($this->model->status == '0') {
            $this->setError('Account disabled');
            return false;
        }
        $adminLoginRetry = Config::get('buildadmin.admin_login_retry');
        if ($adminLoginRetry && $this->model->login_failure >= $adminLoginRetry && time() - $this->model->getData('last_login_time') < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }
        if ($this->model->password != encrypt_password($password, $this->model->salt)) {
            $this->loginFailed();
            $this->setError('Password is incorrect');
            return false;
        }
        if (Config::get('buildadmin.admin_sso')) {
            Token::clear('admin', $this->model->id);
            Token::clear('admin-refresh', $this->model->id);
        }

        if ($keep) {
            $this->setRefreshToken($this->refreshTokenKeepTime);
        }
        $this->loginSuccessful();
        return true;
    }

    /**
     * 设置刷新Token
     * @param int $keepTime
     */
    public function setRefreshToken(int $keepTime = 0): void
    {
        $this->refreshToken = Random::uuid();
        Token::set($this->refreshToken, 'admin-refresh', $this->model->id, $keepTime);
    }

    /**
     * 管理员登录成功
     * @return bool
     */
    public function loginSuccessful(): bool
    {
        if (!$this->model) return false;
        $this->model->startTrans();
        try {
            $this->model->login_failure   = 0;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();
            $this->loginEd = true;

            if (!$this->token) {
                $this->token = Random::uuid();
                Token::set($this->token, 'admin', $this->model->id, $this->keepTime);
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 管理员登录失败
     * @return bool
     */
    public function loginFailed(): bool
    {
        if (!$this->model) return false;
        $this->model->startTrans();
        try {
            $this->model->login_failure++;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return $this->reset();
    }

    /**
     * 退出登录
     * @return bool
     */
    public function logout(): bool
    {
        if (!$this->loginEd) {
            $this->setError('You are not logged in');
            return false;
        }
        return $this->reset();
    }

    /**
     * 是否登录
     * @return bool
     */
    public function isLogin(): bool
    {
        return $this->loginEd;
    }

    /**
     * 获取管理员模型
     * @return Admin
     */
    public function getAdmin(): Admin
    {
        return $this->model;
    }

    /**
     * 获取管理员Token
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 获取管理员刷新Token
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * 获取管理员信息 - 只输出允许输出的字段
     * @return array
     */
    public function getInfo(): array
    {
        if (!$this->model) return [];
        $info                  = $this->model->toArray();
        $info                  = array_intersect_key($info, array_flip($this->getAllowFields()));
        $info['token']         = $this->getToken();
        $info['refresh_token'] = $this->getRefreshToken();
        return $info;
    }

    /**
     * 获取允许输出字段
     * @return array
     */
    public function getAllowFields(): array
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出字段
     * @param $fields
     * @return void
     */
    public function setAllowFields($fields): void
    {
        $this->allowFields = $fields;
    }

    /**
     * 设置Token有效期
     * @param int $keepTime
     * @return void
     */
    public function setKeepTime(int $keepTime = 0): void
    {
        $this->keepTime = $keepTime;
    }

    public function check(string $name, int $uid = 0, string $relation = 'or', string $mode = 'url'): bool
    {
        return parent::check($name, $uid ?: $this->id, $relation, $mode);
    }

    public function getGroups(int $uid = 0): array
    {
        return parent::getGroups($uid ?: $this->id);
    }

    public function getRuleList(int $uid = 0): array
    {
        return parent::getRuleList($uid ?: $this->id);
    }

    public function getRuleIds(int $uid = 0): array
    {
        return parent::getRuleIds($uid ?: $this->id);
    }

    public function getMenus(int $uid = 0): array
    {
        return parent::getMenus($uid ?: $this->id);
    }

    /**
     * 是否是超级管理员
     * @throws Throwable
     */
    public function isSuperAdmin(): bool
    {
        return in_array('*', $this->getRuleIds());
    }

    /**
     * 获取管理员所在分组的所有子级分组
     * @return array
     * @throws Throwable
     */
    public function getAdminChildGroups(): array
    {
        $groupIds = Db::name('admin_group_access')
            ->where('uid', $this->id)
            ->select();
        $children = [];
        foreach ($groupIds as $group) {
            $this->getGroupChildGroups($group['group_id'], $children);
        }
        return array_unique($children);
    }

    /**
     * 获取一个分组下的子分组
     * @param int   $groupId  分组ID
     * @param array $children 存放子分组的变量
     * @return void
     * @throws Throwable
     */
    public function getGroupChildGroups(int $groupId, array &$children): void
    {
        $childrenTemp = AdminGroup::where('pid', $groupId)
            ->where('status', '1')
            ->select();
        foreach ($childrenTemp as $item) {
            $children[] = $item['id'];
            $this->getGroupChildGroups($item['id'], $children);
        }
    }

    /**
     * 获取分组内的管理员
     * @param array $groups
     * @return array 管理员数组
     */
    public function getGroupAdmins(array $groups): array
    {
        return Db::name('admin_group_access')
            ->where('group_id', 'in', $groups)
            ->column('uid');
    }

    /**
     * 获取拥有"所有权限"的分组
     * @param string $dataLimit 数据权限
     * @return array 分组数组
     * @throws Throwable
     */
    public function getAllAuthGroups(string $dataLimit): array
    {
        // 当前管理员拥有的权限
        $rules         = $this->getRuleIds();
        $allAuthGroups = [];
        $groups        = AdminGroup::where('status', '1')->select();
        foreach ($groups as $group) {
            if ($group['rules'] == '*') {
                continue;
            }
            $groupRules = explode(',', $group['rules']);

            // 及时break, array_diff 等没有 in_array 快
            $all = true;
            foreach ($groupRules as $groupRule) {
                if (!in_array($groupRule, $rules)) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                if ($dataLimit == 'allAuth' || ($dataLimit == 'allAuthAndOthers' && array_diff($rules, $groupRules))) {
                    $allAuthGroups[] = $group['id'];
                }
            }
        }
        return $allAuthGroups;
    }

    /**
     * 设置错误消息
     * @param $error
     * @return Auth
     */
    public function setError($error): Auth
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 获取错误消息
     * @return string
     */
    public function getError(): string
    {
        return $this->error ? __($this->error) : '';
    }

    /**
     * 属性重置（注销、登录失败、重新初始化等将单例数据销毁）
     */
    protected function reset(bool $deleteToken = true): bool
    {
        if ($deleteToken && $this->token) {
            Token::delete($this->token);
        }

        $this->token        = '';
        $this->loginEd      = false;
        $this->model        = null;
        $this->refreshToken = '';
        $this->setError('');
        $this->setKeepTime((int)Config::get('buildadmin.admin_token_keep_time'));
        return true;
    }
}