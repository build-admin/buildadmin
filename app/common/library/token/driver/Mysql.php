<?php

namespace app\common\library\token\driver;

use app\common\library\token\Driver;
use think\facade\Db;

/**
 * @see Driver
 */
class Mysql extends Driver
{
    /**
     * 默认配置
     * @var array
     */
    protected $options = [];

    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if ($this->options['name']) {
            $this->handler = Db::connect($this->options['name'])->name($this->options['table']);
        } else {
            $this->handler = Db::name($this->options['table']);
        }
    }

    public function set(string $token, string $type, int $user_id, int $expire = null): bool
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $expiretime = $expire !== 0 ? time() + $expire : 0;
        $token      = $this->getEncryptedToken($token);
        $this->handler->insert(['token' => $token, 'type' => $type, 'user_id' => $user_id, 'createtime' => time(), 'expiretime' => $expiretime]);
        return true;
    }

    public function get(string $token): array
    {
        $data = $this->handler->where('token', $this->getEncryptedToken($token))->find();
        if ($data) {
            if (!$data['expiretime'] || $data['expiretime'] > time()) {
                //返回未加密的token给客户端使用
                $data['token'] = $token;
                //返回剩余有效时间
                $data['expires_in'] = $this->getExpiredIn($data['expiretime']);
                return $data;
            } else {
                self::delete($token);
            }
        }
        return [];
    }

    public function check(string $token, string $type, int $user_id): bool
    {
        $data = $this->get($token);
        return $data && $data['type'] == $type && $data['user_id'] == $user_id;
    }

    public function delete(string $token): bool
    {
        $this->handler->where('token', $this->getEncryptedToken($token))->delete();
        return true;
    }

    public function clear(string $type, int $user_id): bool
    {
        $this->handler->where('type', $type)->where('user_id', $user_id)->delete();
        return true;
    }

}