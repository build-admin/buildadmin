<?php

namespace ba;

use Phinx\Db\Table;
use think\facade\Db;
use think\facade\Config;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\AdapterInterface;

/**
 * 数据表管理类
 * @example TableManager::instance('test_table', ['comment' => '测试表'])->create();
 */
class TableManager
{

    protected static ?AdapterInterface $adapter = null;

    protected static ?AdapterInterface $wrapper = null;

    /**
     * 静态的 Phinx/Db/Table 实例列表
     * @var array
     * @uses Table 数组项
     */
    protected static array $instances = [];

    /**
     * 返回一个 Phinx/Db/Table 实例 用于操作数据表
     * @param string $table               表名
     * @param array  $options             传递给 Phinx/Db/Table 的 options
     * @param bool   $tableContainsPrefix 表名是否已经包含前缀
     * @return Table
     */
    public static function instance(string $table, array $options = [], bool $tableContainsPrefix = false): Table
    {
        if (array_key_exists($table, self::$instances)) {
            return self::$instances[$table];
        }

        if (is_null(self::$adapter)) {
            $config        = static::getDbConfig();
            $factory       = AdapterFactory::instance();
            self::$adapter = $factory->getAdapter($config['adapter'], $config);

            if (!$tableContainsPrefix && is_null(self::$wrapper)) {
                self::$wrapper = $factory->getWrapper('prefix', self::$adapter);
            }
        }

        self::$instances[$table] = new Table($table, $options, $tableContainsPrefix ? self::$adapter : self::$wrapper);
        return self::$instances[$table];
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected static function getDbConfig(): array
    {
        $default = Config::get('database.default');
        $config  = Config::get("database.connections.$default");

        if ($config['deploy'] == 0) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'connection'   => Db::getPdo(),
                'name'         => $config['database'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'connection'   => Db::getPdo(),
                'name'         => explode(',', $config['database'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $table = Config::get('database.migration_table', 'migrations');

        $dbConfig['default_migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }
}