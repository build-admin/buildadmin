<?php

namespace app\common\library;

use app\common\library\token\Driver;
use think\helper\Arr;
use think\Manager;

/**
 * Token 管理类
 * @see Manager 用来加载驱动
 * @mixin Driver
 */
class Token extends Manager
{
    protected $namespace = '\\app\\common\\library\\token\\driver\\';

    /**
     * 默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('buildadmin.token.' . $name, $default);
        }

        return $this->app->config->get('buildadmin.token');
    }

    /**
     * 获取驱动配置
     * @param string $store
     * @param string $name
     * @param null   $default
     * @return array
     */
    public function getStoreConfig(string $store, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("stores.{$store}")) {
            return Arr::get($config, $name, $default);
        }

        throw new \InvalidArgumentException("Store [$store] not found.");
    }

    protected function resolveType(string $name)
    {
        return $this->getStoreConfig($name, 'type', 'mysql');
    }

    protected function resolveConfig(string $name)
    {
        return $this->getStoreConfig($name);
    }

    public function store(string $name = null)
    {
        return $this->driver($name);
    }

    public function set(string $token, string $type, int $user_id, int $expire = null): bool
    {
        return $this->store()->set($token, $type, $user_id, $expire);
    }

    public function get(string $token): array
    {
        return $this->store()->get($token);
    }

    public function check(string $token, string $type, int $user_id): bool
    {
        return $this->store()->check($token, $type, $user_id);
    }

    public function delete(string $token): bool
    {
        return $this->store()->delete($token);
    }

    public function clear(string $type, int $user_id): bool
    {
        return $this->store()->clear($type, $user_id);
    }
}