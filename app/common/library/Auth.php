<?php

namespace app\common\library;

use Throwable;
use ba\Random;
use think\facade\Db;
use think\facade\Event;
use think\facade\Config;
use app\common\model\User;
use think\facade\Validate;
use app\common\facade\Token;

/**
 * 公共权限类（会员权限类）
 * @property int    $id         会员ID
 * @property string $username   会员用户名
 * @property string $nickname   会员昵称
 * @property string $email      会员邮箱
 * @property string $mobile     会员手机号
 * @property string $password   密码密文
 * @property string $salt       密码盐
 */
class Auth extends \ba\Auth
{
    /**
     * 对象实例
     * @var ?Auth
     */
    protected static ?Auth $instance = null;

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
     * @var ?User
     */
    protected ?User $model = null;

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
     * @var int
     */
    protected int $keepTime = 86400;

    /**
     * 允许输出的字段
     * @var array
     */
    protected array $allowFields = ['id', 'username', 'nickname', 'email', 'mobile', 'avatar', 'gender', 'birthday', 'money', 'score', 'join_time', 'motto', 'last_login_time', 'last_login_ip'];

    public function __construct(array $config = [])
    {
        parent::__construct(array_merge([
            'auth_group'        => 'user_group', // 用户组数据表名
            'auth_group_access' => '', // 用户-用户组关系表（关系字段）
            'auth_rule'         => 'user_rule', // 权限规则表
        ], $config));
    }

    /**
     * 魔术方法-会员信息字段
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
     * @param array $options 传递给 /ba/Auth 的参数
     * @return Auth
     */
    public static function instance(array $options = []): Auth
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 根据Token初始化会员登录态
     * @param $token
     * @return bool
     * @throws Throwable
     */
    public function init($token): bool
    {
        if ($this->loginEd) {
            return true;
        }
        if ($this->error) {
            return false;
        }
        $tokenData = Token::get($token);
        if (!$tokenData) {
            return false;
        }
        $userId = intval($tokenData['user_id']);
        if ($tokenData['type'] == 'user' && $userId > 0) {
            $this->model = User::where('id', $userId)->find();
            if (!$this->model) {
                $this->setError('Account not exist');
                return false;
            }
            if ($this->model->status != 'enable') {
                $this->setError('Account disabled');
                return false;
            }
            $this->token = $token;
            $this->loginSuccessful();
            return true;
        } else {
            $this->setError('Token login failed');
            return false;
        }
    }

    /**
     * 会员注册
     * @param string $username
     * @param string $password
     * @param string $mobile
     * @param string $email
     * @param int    $group
     * @param array  $extend
     * @return bool
     */
    public function register(string $username, string $password, string $mobile = '', string $email = '', int $group = 1, array $extend = []): bool
    {
        $validate = Validate::rule([
            'mobile'   => 'mobile|unique:user',
            'email'    => 'email|unique:user',
            'username' => 'regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:user',
            'password' => 'regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
        ]);
        $params   = [
            'username' => $username,
            'password' => $password,
            'mobile'   => $mobile,
            'email'    => $email,
        ];
        if (!$validate->check($params)) {
            $this->setError('Registration parameter error');
            return false;
        }

        $ip   = request()->ip();
        $time = time();
        $salt = Random::build('alnum', 16);
        $data = [
            'password'        => encrypt_password($password, $salt),
            'group_id'        => $group,
            'nickname'        => preg_match("/^1[3-9]\d{9}$/", $username) ? substr_replace($username, '****', 3, 4) : $username,
            'join_ip'         => $ip,
            'join_time'       => $time,
            'last_login_ip'   => $ip,
            'last_login_time' => $time,
            'salt'            => $salt,
            'status'          => 'enable',
        ];
        $data = array_merge($params, $data);
        $data = array_merge($data, $extend);
        Db::startTrans();
        try {
            $this->model = User::create($data);
            $this->token = Random::uuid();
            Token::set($this->token, 'user', $this->model->id, $this->keepTime);
            Db::commit();
            Event::trigger('userRegisterSuccess', $this->model);
        } catch (Throwable $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 会员登录
     * @param string $username
     * @param string $password
     * @param bool   $keepTime
     * @return bool
     * @throws Throwable
     */
    public function login(string $username, string $password, bool $keepTime): bool
    {
        // 判断账户类型
        $accountType = false;
        $validate    = Validate::rule([
            'mobile'   => 'mobile',
            'email'    => 'email',
            'username' => 'regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$',
        ]);
        if ($validate->check(['mobile' => $username])) $accountType = 'mobile';
        if ($validate->check(['email' => $username])) $accountType = 'email';
        if ($validate->check(['username' => $username])) $accountType = 'username';
        if (!$accountType) {
            $this->setError('Account not exist');
            return false;
        }

        $this->model = User::where($accountType, $username)->find();
        if (!$this->model) {
            $this->setError('Account not exist');
            return false;
        }
        if ($this->model->status == 'disable') {
            $this->setError('Account disabled');
            return false;
        }
        $userLoginRetry = Config::get('buildadmin.user_login_retry');
        if ($userLoginRetry && $this->model->login_failure >= $userLoginRetry && time() - $this->model->last_login_time < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }
        if ($this->model->password != encrypt_password($password, $this->model->salt)) {
            $this->loginFailed();
            $this->setError('Password is incorrect');
            return false;
        }
        if (Config::get('buildadmin.user_sso')) {
            Token::clear('user', $this->model->id);
            Token::clear('user-refresh', $this->model->id);
        }

        if ($keepTime) {
            $this->setRefreshToken(2592000);
        }
        $this->loginSuccessful();
        return true;
    }

    /**
     * 直接登录会员账号
     * @param int $userId 用户ID
     * @return bool
     * @throws Throwable
     */
    public function direct(int $userId): bool
    {
        $this->model = User::find($userId);
        if (!$this->model) return false;
        if (Config::get('buildadmin.user_sso')) {
            Token::clear('user', $this->model->id);
            Token::clear('user-refresh', $this->model->id);
        }
        return $this->loginSuccessful();
    }

    /**
     * 检查旧密码是否正确
     * @param $password
     * @return bool
     */
    public function checkPassword($password): bool
    {
        if ($this->model->password != encrypt_password($password, $this->model->salt)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 登录成功
     * @return bool
     */
    public function loginSuccessful(): bool
    {
        if (!$this->model) {
            return false;
        }
        $this->model->startTrans();
        try {
            $this->model->login_failure   = 0;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();
            $this->loginEd = true;

            if (!$this->token) {
                $this->token = Random::uuid();
                Token::set($this->token, 'user', $this->model->id, $this->keepTime);
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
     * 登录失败
     * @return bool
     */
    public function loginFailed(): bool
    {
        if (!$this->model) {
            return false;
        }
        $this->model->startTrans();
        try {
            $this->model->login_failure++;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();

            $this->token   = '';
            $this->loginEd = false;
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->setError($e->getMessage());
            return false;
        }
        $this->model = null;
        return true;
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
        $this->loginEd = false;
        Token::delete($this->token);
        $this->token = '';
        return true;
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
     * 获取会员模型
     * @return User
     */
    public function getUser(): User
    {
        return $this->model;
    }

    /**
     * 获取会员Token
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 设置刷新Token
     * @param int $keepTime
     * @return void
     */
    public function setRefreshToken(int $keepTime = 0): void
    {
        $this->refreshToken = Random::uuid();
        Token::set($this->refreshToken, 'user-refresh', $this->model->id, $keepTime);
    }

    /**
     * 获取会员刷新Token
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * 获取会员信息 - 只输出允许输出的字段
     * @return array
     */
    public function getUserInfo(): array
    {
        if (!$this->model) {
            return [];
        }
        $info                 = $this->model->toArray();
        $info                 = array_intersect_key($info, array_flip($this->getAllowFields()));
        $info['token']        = $this->getToken();
        $info['refreshToken'] = $this->getRefreshToken();
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
     * 是否是拥有所有权限的会员
     * @return bool
     * @throws Throwable
     */
    public function isSuperUser(): bool
    {
        return in_array('*', $this->getRuleIds());
    }

    /**
     * 设置错误消息
     * @param string $error
     * @return Auth
     */
    public function setError(string $error): Auth
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
}