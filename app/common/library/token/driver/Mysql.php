<?php

namespace app\common\library\token\driver;

use Throwable;
use think\facade\Db;
use think\facade\Cache;
use app\common\library\token\Driver;

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
    public function set(string $token, string $type, int $userId, ?int $expire = null): bool
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $expireTime = $expire !== 0 ? time() + $expire : 0;
        $token      = $this->getEncryptedToken($token);
        $this->handler->insert([
            'token'       => $token,
            'type'        => $type,
            'user_id'     => $userId,
            'create_time' => time(),
            'expire_time' => $expireTime,
        ]);

        // 每隔48小时清理一次过期Token
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
    public function get(string $token): array
    {
        $data = $this->handler->where('token', $this->getEncryptedToken($token))->find();
        if (!$data) {
            return [];
        }

        $data['token']      = $token; // 返回未加密的token给客户端使用
        $data['expires_in'] = $this->getExpiredIn($data['expire_time'] ?? 0); // 返回剩余有效时间
        return $data;
    }

    /**
     * @throws Throwable
     */
    public function check(string $token, string $type, int $userId): bool
    {
        $data = $this->get($token);
        if (!$data || ($data['expire_time'] && $data['expire_time'] <= time())) return false;
        return $data['type'] == $type && $data['user_id'] == $userId;
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
    public function clear(string $type, int $userId): bool
    {
        $this->handler->where('type', $type)->where('user_id', $userId)->delete();
        return true;
    }
}