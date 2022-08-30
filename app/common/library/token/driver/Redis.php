<?php

namespace app\common\library\token\driver;

use app\common\library\token\Driver;

/**
 * @see Driver
 */
class Redis extends Driver
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
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('未安装redis扩展');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->handler = new \Redis;
        if ($this->options['persistent']) {
            $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
    }

    public function set(string $token, string $type, int $user_id, int $expire = 0): bool
    {
        if (!$expire) {
            $expire = $this->options['expire'];
        }
        $key = $this->getEncryptedToken($token);
        $value =  $user_id.'-'.$type;
        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }
        //写入会员关联的token,删除时用
        $this->handler->sAdd($this->options['persistent'].$user_id, $key);
        return true;
    }

    public function get(string $token): array
    {
        $key = $this->getEncryptedToken($token);
        $value = $this->handler->get($key);
        if (is_null($value) || false === $value) {
            return [];
        }
        //获取有效期
        $expire = $this->handler->ttl($key);
        $expire = $expire < 0 ? 365 * 86400 : $expire;
        $expiretime = time() + $expire;
        $value = explode('-',$value)[0];
        //解决使用redis方式储存token时api接口Token刷新与检测因expires_in拼写错误报错的BUG
        $result = ['token' => $token, 'user_id' => $value, 'expiretime' => $expiretime, 'expires_in' => $expire];
        return $result;
    }

    public function check(string $token, string $type, int $user_id): bool
    {
        $data = $this->handler->hget($token,$type);
        return $data && $data == $user_id;
    }

    public function delete(string $token): bool
    {
        $data = $this->get($token);
        if ($data) {
            $key = $this->getEncryptedToken($token);
            $user_id = $data['user_id'];
            $this->handler->del($key);
            $this->handler->sRem($this->options['persistent'].$user_id, $key);
        }
        return true;
    }

    public function clear(string $type, int $user_id): bool
    {
        $keys = $this->handler->sMembers($this->options['persistent'].$user_id);
        $this->handler->del($this->options['persistent'].$user_id);
        $this->handler->del($keys);
        return true;
    }

}