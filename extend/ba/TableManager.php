<?php

namespace ba;

use Throwable;
use think\facade\Db;
use think\facade\Config;
use think\migration\db\Table;
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
     * @param string  $table         表名
     * @param array   $options       传递给 Phinx/Db/Table 的 options
     * @param bool    $prefixWrapper 是否使用表前缀包装表名
     * @param ?string $connection    连接配置标识
     * @return Table
     */
    public static function instance(string $table, array $options = [], bool $prefixWrapper = true, ?string $connection = null): Table
    {
        if (array_key_exists($table, self::$instances)) {
            return self::$instances[$table];
        }

        self::adapter($prefixWrapper, $connection);

        self::$instances[$table] = new Table($table, $options, $prefixWrapper ? self::$wrapper : self::$adapter);
        return self::$instances[$table];
    }

    /**
     * 返回一个 Phinx\Db\Adapter\AdapterFactory 实例
     * @param bool    $prefixWrapper 是否使用表前缀包装表名
     * @param ?string $connection    连接配置标识
     * @return AdapterInterface
     */
    public static function adapter(bool $prefixWrapper = true, ?string $connection = null): AdapterInterface
    {
        if (is_null(self::$adapter)) {
            $config        = static::getDbConfig($connection);
            $factory       = AdapterFactory::instance();
            self::$adapter = $factory->getAdapter($config['adapter'], $config);
            if ($prefixWrapper && is_null(self::$wrapper)) {
                self::$wrapper = $factory->getWrapper('prefix', self::$adapter);
            }
        }
        return $prefixWrapper ? self::$wrapper : self::$adapter;
    }

    /**
     * 修改已有数据表的注释
     * Phinx只在新增表时可以设置注释
     * @param string  $name
     * @param string  $comment
     * @param ?string $connection 连接配置标识
     * @return bool
     */
    public static function changeComment(string $name, string $comment, ?string $connection = null): bool
    {
        $name = self::tableName($name, true, $connection);
        try {
            $sql = "ALTER TABLE `$name` COMMENT = '$comment'";
            Db::connect($connection)->execute($sql);
        } catch (Throwable) {
            return false;
        }
        return true;
    }

    /**
     * 数据表名
     * @param string  $table      表名，带不带前缀均可
     * @param bool    $fullName   是否返回带前缀的表名
     * @param ?string $connection 连接配置标识
     * @return string 表名
     */
    public static function tableName(string $table, bool $fullName = true, ?string $connection = null): string
    {
        $connection  = self::getConnection($connection);
        $tablePrefix = config("database.connections.$connection.prefix");
        $pattern     = '/^' . $tablePrefix . '/i';
        return ($fullName ? $tablePrefix : '') . (preg_replace($pattern, '', $table));
    }

    /**
     * 获取数据库配置
     * @param ?string $connection 连接配置标识
     * @return array
     */
    protected static function getDbConfig(?string $connection = null): array
    {
        $connection = self::getConnection($connection);
        $config     = Config::get("database.connections.$connection");

        if ($config['deploy'] == 0) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'connection'   => Db::connect($connection)->getPdo(),
                'name'         => $config['database'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'connection'   => Db::connect($connection)->getPdo(),
                'name'         => explode(',', $config['database'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $table = Config::get('database.migration_table', 'migrations');

        $dbConfig['migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }

    /**
     * 系统是否存在多个数据库连接配置
     */
    public static function isMultiDatabase(): bool
    {
        return count(Config::get("database.connections")) > 1;
    }

    /**
     * 获取数据库连接配置标识
     * @param ?string $source
     * @return string 连接配置标识
     */
    public static function getConnection(?string $source = null): string
    {
        if (is_null($source) || $source === 'default') {
            return Config::get('database.default');
        }
        return $source;
    }
}