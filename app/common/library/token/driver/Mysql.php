<?php

namespace app\common\library\token\driver;

use Throwable;
use think\Response;
use think\facade\Db;
use think\facade\Cache;
use app\common\library\token\Driver;
use think\exception\HttpResponseException;

/**
 * @see Driver
 */
class Mysql extends Driver
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
     */
    public function __construct(array $options = [])
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
        $this->handler->insert(['token' => $token, 'type' => $type, 'user_id' => $user_id, 'create_time' => time(), 'expire_time' => $expireTime]);

        // 每隔48小时清理一次过期缓存
        $time                 = time();
        $lastCacheCleanupTime = Cache::get('last_cache_cleanup_time');
        if (!$lastCacheCleanupTime || $lastCacheCleanupTime < $time - 172800) {
            Cache::set('last_cache_cleanup_time', $time);
            $this->handler->where('expire_time', '<', time())->where('expire_time', '>', 0)->delete();
        }
        return true;
    }

    /**
     * @throws Throwable
     */
    public function get(string $token, bool $expirationException = true): array
    {
        $data = $this->handler->where('token', $this->getEncryptedToken($token))->find();
        if (!$data) {
            return [];
        }
        // 返回未加密的token给客户端使用
        $data['token'] = $token;
        // 返回剩余有效时间
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
        $this->handler->where('token', $this->getEncryptedToken($token))->delete();
        return true;
    }

    /**
     * @throws Throwable
     */
    public function clear(string $type, int $user_id): bool
    {
        $this->handler->where('type', $type)->where('user_id', $user_id)->delete();
        return true;
    }

}