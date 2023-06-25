<?php

namespace app\common\library\token\driver;

use Throwable;
use think\Response;
use BadFunctionCallException;
use app\common\library\token\Driver;
use think\exception\HttpResponseException;

/**
 * @see Driver
 */
class Redis extends Driver
{
    /**
     * 默认配置
     * @var array
     */
    protected array $options = [];

    /**
     * 构造函数
     * @access public
     * @param array $options 参数
     * @throws Throwable
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('redis')) {
            throw new BadFunctionCallException('未安装redis扩展');
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

        if (false !== $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
    }

    /**
     * @throws Throwable
     */
    public function set(string $token, string $type, int $user_id, int $expire = null): bool
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $expireTime = $expire !== 0 ? time() + $expire : 0;
        $token      = $this->getEncryptedToken($token);
        $tokenInfo  = [
            'token'       => $token,
            'type'        => $type,
            'user_id'     => $user_id,
            'create_time' => time(),
            'expire_time' => $expireTime,
        ];
        $tokenInfo  = json_encode($tokenInfo, JSON_UNESCAPED_UNICODE);
        if ($expire) {
            if ($type == 'admin' || $type == 'user') {
                // 增加 redis中的 token 过期时间，以免 token 过期自动刷新永远无法触发
                $expire *= 2;
            }
            $result = $this->handler->setex($token, $expire, $tokenInfo);
        } else {
            $result = $this->handler->set($token, $tokenInfo);
        }
        $this->handler->sAdd($this->getUserKey($user_id), $token);
        return $result;
    }

    /**
     * @throws Throwable
     */
    public function get(string $token, bool $expirationException = true): array
    {
        $key  = $this->getEncryptedToken($token);
        $data = $this->handler->get($key);
        if (is_null($data) || false === $data) {
            return [];
        }
        $data = json_decode($data, true);
        // 返回未加密的token给客户端使用
        $data['token'] = $token;
        // 过期时间
        $data['expires_in'] = $this->getExpiredIn($data['expire_time'] ?? 0);

        if ($data['expire_time'] && $data['expire_time'] <= time() && $expirationException) {
            // token过期-触发前端刷新token
            $response = Response::create(['code' => 409, 'msg' => __('Token expiration'), 'data' => $data], 'json');
            throw new HttpResponseException($response);
        }
        return $data;
    }

    /**
     * @throws Throwable
     */
    public function check(string $token, string $type, int $user_id, bool $expirationException = true): bool
    {
        $data = $this->get($token, $expirationException);
        if (!$data || (!$expirationException && $data['expire_time'] && $data['expire_time'] <= time())) return false;
        return $data['type'] == $type && $data['user_id'] == $user_id;
    }

    /**
     * @throws Throwable
     */
    public function delete(string $token): bool
    {
        $data = $this->get($token, false);
        if ($data) {
            $key     = $this->getEncryptedToken($token);
            $user_id = $data['user_id'];
            $this->handler->del($key);
            $this->handler->sRem($this->getUserKey($user_id), $key);
        }
        return true;
    }

    /**
     * @throws Throwable
     */
    public function clear(string $type, int $user_id): bool
    {
        $keys = $this->handler->sMembers($this->getUserKey($user_id));
        $this->handler->del($this->getUserKey($user_id));
        $this->handler->del($keys);
        return true;
    }

    /**
     * 获取会员的key
     * @param $user_id
     * @return string
     */
    protected function getUserKey($user_id): string
    {
        return $this->options['userprefix'] . $user_id;
    }

}