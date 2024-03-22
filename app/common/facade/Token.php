<?php

namespace app\common\facade;

use think\Facade;
use app\common\library\token\Driver;

/**
 * Token 门面类
 * @see Driver
 * @method array get(string $token) static 获取 token 的数据
 * @method bool set(string $token, string $type, int $userId, int $expire = null) static 设置 token
 * @method bool check(string $token, string $type, int $userId) static 检查token是否有效
 * @method bool delete(string $token) static 删除一个token
 * @method bool clear(string $type, int $userId) static 清理一个用户的所有token
 * @method void tokenExpirationCheck(array $token) static 检查一个token是否过期，过期则抛出token过期异常
 */
class Token extends Facade
{
    protected static function getFacadeClass(): string
    {
        return 'app\common\library\Token';
    }
}