<?php

namespace app\common\library\token;

use think\facade\Config;

/**
 * Token 驱动抽象类
 */
abstract class Driver
{
    /**
     * 具体驱动的句柄 Mysql|Redis
     * @var object
     */
    protected object $handler;

    /**
     * @var array 配置数据
     */
    protected array $options = [];

    /**
     * 设置 token
     * @param string $token  Token
     * @param string $type   Type
     * @param int    $userId 用户ID
     * @param ?int   $expire 过期时间
     * @return bool
     */
    abstract public function set(string $token, string $type, int $userId, ?int $expire = null): bool;

    /**
     * 获取 token 的数据
     * @param string $token Token
     * @return array
     */
    abstract public function get(string $token): array;

    /**
     * 检查token是否有效
     * @param string $token
     * @param string $type
     * @param int    $userId
     * @return bool
     */
    abstract public function check(string $token, string $type, int $userId): bool;

    /**
     * 删除一个token
     * @param string $token
     * @return bool
     */
    abstract public function delete(string $token): bool;

    /**
     * 清理一个用户的所有token
     * @param string $type
     * @param int    $userId
     * @return bool
     */
    abstract public function clear(string $type, int $userId): bool;

    /**
     * 返回句柄对象
     * @access public
     * @return object|null
     */
    public function handler(): ?object
    {
        return $this->handler;
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getEncryptedToken(string $token): string
    {
        $config = Config::get('buildadmin.token');
        return hash_hmac($config['algo'], $token, $config['key']);
    }

    /**
     * @param int $expireTime
     * @return int
     */
    protected function getExpiredIn(int $expireTime): int
    {
        return $expireTime ? max(0, $expireTime - time()) : 365 * 86400;
    }
}