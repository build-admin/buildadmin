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
 */
class TableManager
{
    /**
     * 返回一个 Phinx/Db/Table 实例 用于操作数据表
     * @param string  $table         表名
     * @param array   $options       传递给 Phinx/Db/Table 的 options
     * @param bool    $prefixWrapper 是否使用表前缀包装表名
     * @param ?string $connection    连接配置标识
     * @return Table
     * @throws Throwable
     */
    public static function phinxTable(string $table, array $options = [], bool $prefixWrapper = true, ?string $connection = null): Table
    {
        return new Table($table, $options, self::phinxAdapter($prefixWrapper, $connection));
    }

    /**
     * 返回 Phinx\Db\Adapter\AdapterFactory （适配器/连接驱动）实例
     * @param bool    $prefixWrapper 是否使用表前缀包装表名
     * @param ?string $connection    连接配置标识
     * @return AdapterInterface
     * @throws Throwable
     */
    public static function phinxAdapter(bool $prefixWrapper = true, ?string $connection = null): AdapterInterface
    {
        $config  = static::getPhinxDbConfig($connection);
        $factory = AdapterFactory::instance();
        $adapter = $factory->getAdapter($config['adapter'], $config);
        if ($prefixWrapper) return $factory->getWrapper('prefix', $adapter);
        return $adapter;
    }

    /**
     * 数据表名
     * @param string  $table      表名，带不带前缀均可
     * @param bool    $fullName   是否返回带前缀的表名
     * @param ?string $connection 连接配置标识
     * @return string 表名
     * @throws Exception
     */
    public static function tableName(string $table, bool $fullName = true, ?string $connection = null): string
    {
        $connection = self::getConnectionConfig($connection);
        $pattern    = '/^' . $connection['prefix'] . '/i';
        return ($fullName ? $connection['prefix'] : '') . (preg_replace($pattern, '', $table));
    }

    /**
     * 数据表列表
     * @param ?string $connection 连接配置标识
     * @throws Exception
     */
    public static function getTableList(?string $connection = null): array
    {
        $tableList  = [];
        $config     = self::getConnectionConfig($connection);
        $connection = self::getConnection($connection);
        $tables     = Db::connect($connection)->query("SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.TABLES WHERE table_schema = ? ", [$config['database']]);
        foreach ($tables as $row) {
            $tableList[$row['TABLE_NAME']] = $row['TABLE_NAME'] . ($row['TABLE_COMMENT'] ? ' - ' . $row['TABLE_COMMENT'] : '');
        }
        return $tableList;
    }

    /**
     * 获取数据表所有列
     * @param string  $table            数据表名
     * @param bool    $onlyCleanComment 只要干净的字段注释信息
     * @param ?string $connection       连接配置标识
     * @throws Throwable
     */
    public static function getTableColumns(string $table, bool $onlyCleanComment = false, ?string $connection = null): array
    {
        if (!$table) return [];

        $table      = self::tableName($table, true, $connection);
        $config     = self::getConnectionConfig($connection);
        $connection = self::getConnection($connection);

        // 从数据库中获取表字段信息
        // Phinx 目前无法正确获取到列注释信息，故使用 sql
        $sql        = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        $columnList = Db::connect($connection)->query($sql, [$config['database'], $table]);

        $fieldList = [];
        foreach ($columnList as $item) {
            if ($onlyCleanComment) {
                $fieldList[$item['COLUMN_NAME']] = '';
                if ($item['COLUMN_COMMENT']) {
                    $comment                         = explode(':', $item['COLUMN_COMMENT']);
                    $fieldList[$item['COLUMN_NAME']] = $comment[0];
                }
                continue;
            }
            $fieldList[$item['COLUMN_NAME']] = $item;
        }
        return $fieldList;
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
        if (!$source || $source === 'default') {
            return Config::get('database.default');
        }
        return $source;
    }

    /**
     * 获取某个数据库连接的配置数组
     * @param ?string $connection 连接配置标识
     * @throws Exception
     */
    public static function getConnectionConfig(?string $connection = null): array
    {
        $connection = self::getConnection($connection);
        $connection = config("database.connections.$connection");
        if (!is_array($connection)) {
            throw new Exception('Database connection configuration error');
        }

        // 分布式
        if ($connection['deploy'] == 1) {
            $keys = ['type', 'hostname', 'database', 'username', 'password', 'hostport', 'charset', 'prefix'];
            foreach ($connection as $key => $item) {
                if (in_array($key, $keys)) {
                    $connection[$key] = is_array($item) ? $item[0] : explode(',', $item)[0];
                }
            }
        }
        return $connection;
    }

    /**
     * 获取 Phinx 适配器需要的数据库配置
     * @param ?string $connection 连接配置标识
     * @return array
     * @throws Throwable
     */
    protected static function getPhinxDbConfig(?string $connection = null): array
    {
        $config     = self::getConnectionConfig($connection);
        $connection = self::getConnection($connection);
        $db         = Db::connect($connection);

        // 数据库为懒连接，执行 sql 命令为 $db 实例连接数据库
        $db->query('SELECT 1');

        $table = Config::get('database.migration_table', 'migrations');
        return [
            'adapter'         => $config['type'],
            'connection'      => $db->getPdo(),
            'name'            => $config['database'],
            'table_prefix'    => $config['prefix'],
            'migration_table' => $config['prefix'] . $table,
        ];
    }
}