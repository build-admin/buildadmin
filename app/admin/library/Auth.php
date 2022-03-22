<?php

namespace app\admin\library;

use app\admin\model\Admin;
use ba\Random;
use think\Exception;
use think\facade\Config;
use app\common\facade\Token;
use think\facade\Db;

/**
 * 管理员权限类
 */
class Auth extends \ba\Auth
{
    /**
     * @var bool 是否登录
     */
    protected $logined = false;
    /**
     * @var string 错误消息
     */
    protected $error = '';
    /**
     * @var Admin Model实例
     */
    protected $model = null;
    /**
     * @var string 令牌
     */
    protected $token = '';
    /**
     * @var string 刷新令牌
     */
    protected $refreshToken = '';
    /**
     * @var int 令牌默认有效期
     */
    protected $keeptime = 86400;
    /**
     * @var string[] 允许输出的字段
     */
    protected $allowFields = ['id', 'username', 'nickname', 'avatar', 'lastlogintime'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 魔术方法-管理员信息字段
     * @param $name
     * @return null|string 字段信息
     */
    public function __get($name)
    {
        return $this->model ? $this->model->$name : null;
    }

    /**
     * 根据Token初始化管理员登录态
     * @param $token
     * @return bool
     */
    public function init($token)
    {
        if ($this->logined) {
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
        } else {
            $this->setError('Token login failed');
            return false;
        }
    }

    /**
     * 管理员登录
     * @param      $username
     * @param      $password
     * @param bool $keeptime
     * @return bool
     */
    public function login($username, $password, $keeptime = false)
    {
        $this->model = Admin::where('username', $username)->find();
        if (!$this->model) {
            $this->setError('Username is incorrect');
            return false;
        }
        if ($this->model['status'] == '0') {
            $this->setError('Administrator disabled');
            return false;
        }
        $adminLoginRetry = Config::get('buildadmin.admin_login_retry');
        if ($adminLoginRetry && $this->model->loginfailure >= $adminLoginRetry && time() - $this->model->lastlogintime < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }
        if ($this->model->password != Admin::encryptPassword($password, $this->model->salt)) {
            $this->loginFailed();
            $this->setError('Password is incorrect');
            return false;
        }

        if ($keeptime) {
            $this->setRefreshToken(2592000);
        }
        $this->loginSuccessful();
        return true;
    }

    /**
     * 设置刷新Token
     * @param int $keeptime
     */
    public function setRefreshToken($keeptime = 0)
    {
        $this->refreshToken = Random::uuid();
        Token::set($this->refreshToken, 'admin-refresh', $this->model->id, $this->keeptime);
    }

    /**
     * 管理员登录成功
     * @return bool
     */
    public function loginSuccessful()
    {
        if (!$this->model) {
            return false;
        }
        Db::startTrans();
        try {
            $this->model->loginfailure  = 0;
            $this->model->lastlogintime = time();
            $this->model->lastloginip   = request()->ip();
            $this->model->save();
            $this->logined = true;

            if (!$this->token) {
                $this->token = Random::uuid();
                Token::set($this->token, 'admin', $this->model->id, $this->keeptime);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 管理员登录失败
     * @return bool
     */
    public function loginFailed()
    {
        if (!$this->model) {
            return false;
        }
        Db::startTrans();
        try {
            $this->model->loginfailure++;
            $this->model->lastlogintime = time();
            $this->model->lastloginip   = request()->ip();
            $this->model->save();

            $this->token   = '';
            $this->model   = null;
            $this->logined = false;
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 退出登录
     * @return bool
     */
    public function logout()
    {
        if (!$this->logined) {
            $this->setError('You are not logged in');
            return false;
        }
        $this->logined = false;
        Token::delete($this->token);
        $this->token = '';
        return true;
    }

    /**
     * 是否登录
     * @return bool
     */
    public function isLogin()
    {
        return $this->logined;
    }

    /**
     * 获取管理员模型
     * @return null
     */
    public function getAdmin()
    {
        return $this->model;
    }

    /**
     * 获取管理员Token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 获取管理员刷新Token
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * 获取管理员信息 - 只输出允许输出的字段
     * @return array
     */
    public function getInfo()
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
     * @return string[]
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 检测一个方法是否在传递的数组内
     * @return bool
     */
    public function actionInArr($arr = [])
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        if (in_array(strtolower(request()->action()), $arr) || in_array('*', $arr)) {
            return true;
        }
        return false;
    }

    /**
     * 设置允许输出字段
     * @param $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 设置Token有效期
     * @param int $keeptime
     */
    public function setKeeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    public function check($name, $uid = null, $relation = 'or', $mode = 'url')
    {
        return parent::check($name, $uid ?: $this->id, $relation, $mode);
    }

    public function getGroups($uid = null)
    {
        return parent::getGroups($uid ?: $this->id);
    }

    public function getRuleList($uid = null)
    {
        return parent::getRuleList($uid ?: $this->id);
    }

    public function getRuleIds($uid = null)
    {
        return parent::getRuleIds($uid ?: $this->id);
    }

    public function getMenus($uid = null)
    {
        return parent::getMenus($uid ?: $this->id);
    }

    public function isSuperAdmin()
    {
        return in_array('*', $this->getRuleIds());
    }

    /**
     * 设置错误消息
     * @param $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 获取错误消息
     * @return int|mixed|string|null
     */
    public function getError()
    {
        return $this->error ? __($this->error) : '';
    }
}