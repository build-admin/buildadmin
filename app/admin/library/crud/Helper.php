<?php

namespace app\admin\library\crud;

use Throwable;
use ba\Filesystem;
use think\Exception;
use ba\TableManager;
use think\facade\Db;
use app\common\library\Menu;
use app\admin\model\AdminRule;
use app\admin\model\CrudLog;
use ba\Exception as BaException;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Adapter\AdapterInterface;

class Helper
{
    /**
     * 内部保留词
     * @var array
     */
    protected static array $reservedKeywords = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone',
        'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
        'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends',
        'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once',
        'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private',
        'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try',
        'unset', 'use', 'var', 'while', 'xor', 'yield', 'match', 'readonly', 'fn',
    ];

    /**
     * 预设控制器和模型文件位置
     * @var array
     */
    protected static array $parseNamePresets = [
        'controller' => [
            'user'        => ['user', 'user'],
            'admin'       => ['auth', 'admin'],
            'admin_group' => ['auth', 'group'],
            'attachment'  => ['routine', 'attachment'],
            'admin_rule'  => ['auth', 'rule'],
        ],
        'model'      => [],
        'validate'   => [],
    ];

    /**
     * 子级菜单数组(权限节点)
     * @var array
     */
    protected static array $menuChildren = [
        ['type' => 'button', 'title' => '查看', 'name' => '/index', 'status' => '1'],
        ['type' => 'button', 'title' => '添加', 'name' => '/add', 'status' => '1'],
        ['type' => 'button', 'title' => '编辑', 'name' => '/edit', 'status' => '1'],
        ['type' => 'button', 'title' => '删除', 'name' => '/del', 'status' => '1'],
        ['type' => 'button', 'title' => '快速排序', 'name' => '/sortable', 'status' => '1'],
    ];

    /**
     * 输入框类型的识别规则
     * @var array
     */
    protected static array $inputTypeRule = [
        // 开关组件
        [
            'type'   => ['tinyint', 'int', 'enum'],
            'suffix' => ['switch', 'toggle'],
            'value'  => 'switch',
        ],
        [
            'column_type' => ['tinyint(1)', 'char(1)', 'tinyint(1) unsigned'],
            'suffix'      => ['switch', 'toggle'],
            'value'       => 'switch',
        ],
        // 富文本-识别规则和textarea重合,优先识别为富文本
        [
            'type'   => ['longtext', 'text', 'mediumtext', 'smalltext', 'tinytext', 'bigtext'],
            'suffix' => ['content', 'editor'],
            'value'  => 'editor',
        ],
        // textarea
        [
            'type'   => ['varchar'],
            'suffix' => ['textarea', 'multiline', 'rows'],
            'value'  => 'textarea',
        ],
        // Array
        [
            'suffix' => ['array'],
            'value'  => 'array',
        ],
        // 时间选择器-字段类型为int同时以['time', 'datetime']结尾
        [
            'type'   => ['int'],
            'suffix' => ['time', 'datetime'],
            'value'  => 'timestamp',
        ],
        [
            'type'  => ['datetime', 'timestamp'],
            'value' => 'datetime',
        ],
        [
            'type'  => ['date'],
            'value' => 'date',
        ],
        [
            'type'  => ['year'],
            'value' => 'year',
        ],
        [
            'type'  => ['time'],
            'value' => 'time',
        ],
        // 单选select
        [
            'suffix' => ['select', 'list', 'data'],
            'value'  => 'select',
        ],
        // 多选select
        [
            'suffix' => ['selects', 'multi', 'lists'],
            'value'  => 'selects',
        ],
        // 远程select
        [
            'suffix' => ['_id'],
            'value'  => 'remoteSelect',
        ],
        // 远程selects
        [
            'suffix' => ['_ids'],
            'value'  => 'remoteSelects',
        ],
        // 城市选择器
        [
            'suffix' => ['city'],
            'value'  => 'city',
        ],
        // 单图上传
        [
            'suffix' => ['image', 'avatar'],
            'value'  => 'image',
        ],
        // 多图上传
        [
            'suffix' => ['images', 'avatars'],
            'value'  => 'images',
        ],
        // 文件上传
        [
            'suffix' => ['file'],
            'value'  => 'file',
        ],
        // 多文件上传
        [
            'suffix' => ['files'],
            'value'  => 'files',
        ],
        // icon选择器
        [
            'suffix' => ['icon'],
            'value'  => 'icon',
        ],
        // 单选框
        [
            'column_type' => ['tinyint(1)', 'char(1)', 'tinyint(1) unsigned'],
            'suffix'      => ['status', 'state', 'type'],
            'value'       => 'radio',
        ],
        // 数字输入框
        [
            'suffix' => ['number', 'int', 'num'],
            'value'  => 'number',
        ],
        [
            'type'  => ['bigint', 'int', 'mediumint', 'smallint', 'tinyint', 'decimal', 'double', 'float'],
            'value' => 'number',
        ],
        // 富文本-低权重
        [
            'type'  => ['longtext', 'text', 'mediumtext', 'smalltext', 'tinytext', 'bigtext'],
            'value' => 'textarea',
        ],
        // 单选框-低权重
        [
            'type'  => ['enum'],
            'value' => 'radio',
        ],
        // 多选框
        [
            'type'  => ['set'],
            'value' => 'checkbox',
        ],
        // 颜色选择器
        [
            'suffix' => ['color'],
            'value'  => 'color',
        ],
    ];

    /**
     * 预设WEB端文件位置
     * @var array
     */
    protected static array $parseWebDirPresets = [
        'lang'  => [],
        'views' => [
            'user'        => ['user', 'user'],
            'admin'       => ['auth', 'admin'],
            'admin_group' => ['auth', 'group'],
            'attachment'  => ['routine', 'attachment'],
            'admin_rule'  => ['auth', 'rule'],
        ],
    ];

    /**
     * 添加时间字段
     * @var string
     */
    protected static string $createTimeField = 'create_time';

    /**
     * 更新时间字段
     * @var string
     */
    protected static string $updateTimeField = 'update_time';

    /**
     * 属性的类型对照表
     * @var array
     */
    protected static array $attrType = [
        'controller' => [
            'preExcludeFields' => 'array|string',
            'quickSearchField' => 'string|array',
            'withJoinTable'    => 'array',
            'defaultSortField' => 'string|array',
        ],
    ];

    /**
     * 获取字段字典数据
     * @param array  $dict              存储字典数据的变量
     * @param array  $field             字段数据
     * @param string $lang              语言
     * @param string $translationPrefix 翻译前缀
     */
    public static function getDictData(array &$dict, array $field, string $lang, string $translationPrefix = ''): array
    {
        if (!$field['comment']) return [];
        $comment = str_replace(['，', '：'], [',', ':'], $field['comment']);
        if (stripos($comment, ':') !== false && stripos($comment, ',') && stripos($comment, '=') !== false) {
            [$fieldTitle, $item] = explode(':', $comment);
            $dict[$translationPrefix . $field['name']] = $lang == 'en' ? $field['name'] : $fieldTitle;
            foreach (explode(',', $item) as $v) {
                $valArr = explode('=', $v);
                if (count($valArr) == 2) {
                    [$key, $value] = $valArr;
                    $dict[$translationPrefix . $field['name'] . ' ' . $key] = $lang == 'en' ? $field['name'] . ' ' . $key : $value;
                }
            }
        } else {
            $dict[$translationPrefix . $field['name']] = $lang == 'en' ? $field['name'] : $comment;
        }
        return $dict;
    }

    /**
     * 记录CRUD状态
     * @param array $data CRUD记录数据
     * @return int 记录ID
     */
    public static function recordCrudStatus(array $data): int
    {
        if (isset($data['id'])) {
            CrudLog::where('id', $data['id'])
                ->update([
                    'status' => $data['status'],
                ]);
            return $data['id'];
        }
        $log = CrudLog::create([
            'table_name' => $data['table']['name'],
            'table'      => $data['table'],
            'fields'     => $data['fields'],
            'status'     => $data['status'],
        ]);
        return $log->id;
    }

    /**
     * 获取 Phinx 的字段类型数据
     * @param string $type  字段类型
     * @param array  $field 字段数据
     * @return array
     */
    public static function getPhinxFieldType(string $type, array $field): array
    {
        if ($type == 'tinyint') {
            if ((isset($field['dataType']) && $field['dataType'] == 'tinyint(1)') || $field['default'] == '1') {
                $type = 'boolean';
            }
        }
        $phinxFieldTypeMap = [
            // 数字
            'tinyint'    => ['type' => AdapterInterface::PHINX_TYPE_INTEGER, 'limit' => MysqlAdapter::INT_TINY],
            'smallint'   => ['type' => AdapterInterface::PHINX_TYPE_INTEGER, 'limit' => MysqlAdapter::INT_SMALL],
            'mediumint'  => ['type' => AdapterInterface::PHINX_TYPE_INTEGER, 'limit' => MysqlAdapter::INT_MEDIUM],
            'int'        => ['type' => AdapterInterface::PHINX_TYPE_INTEGER, 'limit' => null],
            'bigint'     => ['type' => AdapterInterface::PHINX_TYPE_BIG_INTEGER, 'limit' => null],
            'boolean'    => ['type' => AdapterInterface::PHINX_TYPE_BOOLEAN, 'limit' => null],
            // 文本
            'varchar'    => ['type' => AdapterInterface::PHINX_TYPE_STRING, 'limit' => null],
            'tinytext'   => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => MysqlAdapter::TEXT_TINY],
            'mediumtext' => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => MysqlAdapter::TEXT_MEDIUM],
            'longtext'   => ['type' => AdapterInterface::PHINX_TYPE_TEXT, 'limit' => MysqlAdapter::TEXT_LONG],
            'tinyblob'   => ['type' => AdapterInterface::PHINX_TYPE_BLOB, 'limit' => MysqlAdapter::BLOB_TINY],
            'mediumblob' => ['type' => AdapterInterface::PHINX_TYPE_BLOB, 'limit' => MysqlAdapter::BLOB_MEDIUM],
            'longblob'   => ['type' => AdapterInterface::PHINX_TYPE_BLOB, 'limit' => MysqlAdapter::BLOB_LONG],
        ];
        return array_key_exists($type, $phinxFieldTypeMap) ? $phinxFieldTypeMap[$type] : ['type' => $type, 'limit' => null];
    }

    /**
     * 分析字段limit和精度
     * @param string $type  字段类型
     * @param array  $field 字段数据
     * @return array ['limit' => 10, 'precision' => null, 'scale' => null]
     */
    public static function analyseFieldLimit(string $type, array $field): array
    {
        $fieldType = [
            'decimal' => ['decimal', 'double', 'float'],
            'values'  => ['enum', 'set'],
        ];

        $dataTypeLimit = self::dataTypeLimit($field['dataType'] ?? '');
        if (in_array($type, $fieldType['decimal'])) {
            if ($dataTypeLimit) {
                return ['precision' => $dataTypeLimit[0], 'scale' => $dataTypeLimit[1] ?? 0];
            }
            $scale = isset($field['precision']) ? intval($field['precision']) : 0;
            return ['precision' => $field['length'] ?: 10, 'scale' => $scale];
        } elseif (in_array($type, $fieldType['values'])) {
            foreach ($dataTypeLimit as &$item) {
                $item = str_replace(['"', "'"], '', $item);
            }
            return ['values' => $dataTypeLimit];
        } else {
            if ($dataTypeLimit && $dataTypeLimit[0]) {
                return ['limit' => $dataTypeLimit[0]];
            } elseif (isset($field['length'])) {
                return ['limit' => $field['length']];
            }
        }
        return [];
    }

    public static function dataTypeLimit(string $dataType): array
    {
        preg_match("/\((.*?)\)/", $dataType, $matches);
        if (isset($matches[1]) && $matches[1]) {
            return explode(',', trim($matches[1], ','));
        }
        return [];
    }

    public static function analyseFieldDefault(array $field): mixed
    {
        if (strtolower((string)$field['default']) == 'null') {
            return null;
        }
        return match ($field['default']) {
            'empty string' => '',
            default => $field['default'],
        };
    }

    public static function searchArray($fields, callable $myFunction): array|bool
    {
        foreach ($fields as $key => $field) {
            if (call_user_func($myFunction, $field, $key)) {
                return $field;
            }
        }
        return false;
    }

    /**
     * 获取 Phinx 格式的字段数据
     * @param array $field
     * @return array
     */
    public static function getPhinxFieldData(array $field): array
    {
        $conciseType   = self::analyseFieldType($field);
        $phinxTypeData = self::getPhinxFieldType($conciseType, $field);

        $phinxColumnOptions = self::analyseFieldLimit($conciseType, $field);
        if (!is_null($phinxTypeData['limit'])) {
            $phinxColumnOptions['limit'] = $phinxTypeData['limit'];
        }

        // 无默认值字段
        $noDefaultValueFields = [
            'text', 'blob', 'geometry', 'geometrycollection', 'json', 'linestring', 'longblob', 'longtext', 'mediumblob',
            'mediumtext', 'multilinestring', 'multipoint', 'multipolygon', 'point', 'polygon', 'tinyblob',
        ];
        if ($field['default'] != 'none' && !in_array($conciseType, $noDefaultValueFields)) {
            $phinxColumnOptions['default'] = self::analyseFieldDefault($field);
        }

        $phinxColumnOptions['null']     = (bool)$field['null'];
        $phinxColumnOptions['comment']  = $field['comment'];
        $phinxColumnOptions['signed']   = !$field['unsigned'];
        $phinxColumnOptions['identity'] = $field['autoIncrement'];
        return [
            'type'    => $phinxTypeData['type'],
            'options' => $phinxColumnOptions,
        ];
    }

    /**
     * 表字段排序
     * @param string $tableName    表名
     * @param array  $fields       字段数据
     * @param array  $designChange 前端字段改变数据
     * @return void
     */
    public static function updateFieldOrder(string $tableName, array $fields, array $designChange): void
    {
        if ($designChange) {
            $table = TableManager::instance($tableName, [], false);
            foreach ($designChange as $item) {
                if (!$item['sync']) continue;

                if (!empty($item['after'])) {

                    $fieldName = in_array($item['type'], ['add-field', 'change-field-name']) ? $item['newName'] : $item['oldName'];

                    $field    = self::searchArray($fields, function ($field) use ($fieldName) {
                        return $field['name'] == $fieldName;
                    });
                    $dataType = self::analyseFieldDataType($field);
                    $sql      = "ALTER TABLE `$tableName` MODIFY COLUMN `$fieldName` $dataType";
                    if ($item['after'] == 'FIRST FIELD') {
                        // 设为第一个字段
                        $sql .= ' FIRST';
                    } else {
                        $sql .= " AFTER `{$item['after']}`";
                    }
                    Db::execute($sql);

                    // 使用 Phinx 再更新一遍字段，不然字段注释等数据丢失
                    // think-migration 使用了自行维护的 Phinx，并不支持直接将字段设置为第一个，所以调整排序直接使用 SQL
                    $phinxFieldData = self::getPhinxFieldData($field);
                    $table->changeColumn($fieldName, $phinxFieldData['type'], $phinxFieldData['options']);
                }
            }
            $table->update();
        }
    }

    /**
     * 表设计处理
     * @param array $table  表数据
     * @param array $fields 字段数据
     * @return array
     * @throws Throwable
     */
    public static function handleTableDesign(array $table, array $fields): array
    {
        $name         = TableManager::tableName($table['name']);
        $comment      = $table['comment'] ?? '';
        $designChange = $table['designChange'] ?? [];
        $adapter      = TableManager::adapter(false);

        $pk = self::searchArray($fields, function ($item) {
            return $item['primaryKey'];
        });
        $pk = $pk ? $pk['name'] : '';

        if ($adapter->hasTable($name)) {
            // 更新表
            TableManager::changeComment($name, $comment);
            if ($designChange) {
                $table = TableManager::instance($name, [], false);

                // 改名和删除操作优先
                foreach ($designChange as $item) {

                    if (!$item['sync']) continue;

                    if (in_array($item['type'], ['change-field-name', 'del-field']) && !$table->hasColumn($item['oldName'])) {
                        // 字段不存在
                        throw new BaException(__($item['type'] . ' fail not exist', [$item['oldName']]));
                    }

                    if ($item['type'] == 'change-field-name') {
                        $table->renameColumn($item['oldName'], $item['newName']);

                        // 改名后使用 Phinx 再更新一遍字段，不然字段注释等数据丢失
                        $phinxFieldData = self::getPhinxFieldData(self::searchArray($fields, function ($field) use ($item) {
                            return $field['name'] == $item['newName'];
                        }));
                        $table->changeColumn($item['newName'], $phinxFieldData['type'], $phinxFieldData['options']);
                    } elseif ($item['type'] == 'del-field') {
                        $table->removeColumn($item['oldName']);
                    }
                }

                // 修改字段属性和添加字段操作
                foreach ($designChange as $item) {

                    if (!$item['sync']) continue;

                    if ($item['type'] == 'change-field-attr') {

                        if (!$table->hasColumn($item['oldName'])) {
                            // 字段不存在
                            throw new BaException(__($item['type'] . ' fail not exist', [$item['oldName']]));
                        }

                        $phinxFieldData = self::getPhinxFieldData(self::searchArray($fields, function ($field) use ($item) {
                            return $field['name'] == $item['oldName'];
                        }));
                        $table->changeColumn($item['oldName'], $phinxFieldData['type'], $phinxFieldData['options']);
                    } elseif ($item['type'] == 'add-field') {

                        if ($table->hasColumn($item['newName'])) {
                            // 字段已经存在
                            throw new BaException(__($item['type'] . ' fail exist', [$item['newName']]));
                        }

                        $phinxFieldData = self::getPhinxFieldData(self::searchArray($fields, function ($field) use ($item) {
                            return $field['name'] == $item['newName'];
                        }));
                        $table->addColumn($item['newName'], $phinxFieldData['type'], $phinxFieldData['options']);
                    }
                }
                $table->update();

                // 表更新结构完成再处理字段排序
                self::updateFieldOrder($name, $fields, $designChange);
            }
        } else {
            // 创建表
            $table = TableManager::instance($name, [
                'id'          => false,
                'comment'     => $comment,
                'row_format'  => 'DYNAMIC',
                'primary_key' => $pk,
                'collation'   => 'utf8mb4_unicode_ci',
            ], false);
            foreach ($fields as $field) {
                $phinxFieldData = self::getPhinxFieldData($field);
                $table->addColumn($field['name'], $phinxFieldData['type'], $phinxFieldData['options']);
            }
            $table->create();
        }

        return [$pk];
    }

    /**
     * 解析文件数据
     * @throws Throwable
     */
    public static function parseNameData($app, $table, $type, $value = ''): array
    {
        $pathArr = [];
        if ($value) {
            $value        = str_replace('.php', '', $value);
            $value        = str_replace(['.', '/', '\\', '_'], '/', $value);
            $pathArrTemp  = explode('/', $value);
            $redundantDir = [
                'app' => 0,
                $app  => 1,
                $type => 2,
            ];
            foreach ($pathArrTemp as $key => $item) {
                if (!array_key_exists($item, $redundantDir) || $key !== $redundantDir[$item]) {
                    $pathArr[] = $item;
                }
            }
        } else {
            if (isset(self::$parseNamePresets[$type]) && array_key_exists($table, self::$parseNamePresets[$type])) {
                $pathArr = self::$parseNamePresets[$type][$table];
            } else {
                $table   = str_replace(['.', '/', '\\', '_'], '/', $table);
                $pathArr = explode('/', $table);
            }
        }
        $originalLastName = array_pop($pathArr);
        $pathArr          = array_map('strtolower', $pathArr);
        $lastName         = ucfirst($originalLastName);

        // 类名不能为内部关键字
        if (in_array(strtolower($originalLastName), self::$reservedKeywords)) {
            throw new Exception('Unable to use internal variable:' . $lastName);
        }

        $appDir       = app()->getBasePath() . $app . DIRECTORY_SEPARATOR;
        $namespace    = "app\\$app\\$type" . ($pathArr ? '\\' . implode('\\', $pathArr) : '');
        $parseFile    = $appDir . $type . DIRECTORY_SEPARATOR . ($pathArr ? implode(DIRECTORY_SEPARATOR, $pathArr) . DIRECTORY_SEPARATOR : '') . $lastName . '.php';
        $rootFileName = $namespace . "/$lastName" . '.php';

        return [
            'lastName'         => $lastName,
            'originalLastName' => $originalLastName,
            'path'             => $pathArr,
            'namespace'        => $namespace,
            'parseFile'        => Filesystem::fsFit($parseFile),
            'rootFileName'     => Filesystem::fsFit($rootFileName),
        ];
    }

    public static function parseWebDirNameData($table, $type, $value = ''): array
    {
        $pathArr = [];
        if ($value) {
            $value        = str_replace(['.', '/', '\\', '_'], '/', $value);
            $pathArrTemp  = explode('/', $value);
            $redundantDir = [
                'web'     => 0,
                'src'     => 1,
                'views'   => 2,
                'lang'    => 2,
                'backend' => 3,
                'pages'   => 3,
                'en'      => 4,
                'zh-cn'   => 4,
            ];
            foreach ($pathArrTemp as $key => $item) {
                if (!array_key_exists($item, $redundantDir) || $key !== $redundantDir[$item]) {
                    $pathArr[] = $item;
                }
            }
        } else {
            if (array_key_exists($table, self::$parseWebDirPresets[$type])) {
                $pathArr = self::$parseWebDirPresets[$type][$table];
            } else {
                $table   = str_replace(['.', '/', '\\', '_'], '/', $table);
                $pathArr = explode('/', $table);
            }
        }
        $originalLastName = array_pop($pathArr);
        $pathArr          = array_map('strtolower', $pathArr);
        $lastName         = lcfirst($originalLastName);

        $webDir['path']             = $pathArr;
        $webDir['lastName']         = $lastName;
        $webDir['originalLastName'] = $originalLastName;
        if ($type == 'views') {
            $webDir['views'] = "web/src/views/backend" . ($pathArr ? '/' . implode('/', $pathArr) : '') . "/$lastName";
        } elseif ($type == 'lang') {
            $webDir['lang'] = array_merge($pathArr, [$lastName]);
            $langDir        = ['en', 'zh-cn'];
            foreach ($langDir as $item) {
                $webDir[$item] = "web/src/lang/backend/$item" . ($pathArr ? '/' . implode('/', $pathArr) : '') . "/$lastName";
            }
        }
        foreach ($webDir as &$item) {
            if (is_string($item)) $item = Filesystem::fsFit($item);
        }
        return $webDir;
    }

    /**
     * 获取菜单name、path
     * @param array $webDir
     * @return string
     */
    public static function getMenuName(array $webDir): string
    {
        return ($webDir['path'] ? implode('/', $webDir['path']) . '/' : '') . $webDir['originalLastName'];
    }

    /**
     * 获取基础模板文件路径
     * @param string $name
     * @return string
     */
    public static function getStubFilePath(string $name): string
    {
        return app_path() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'crud' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . Filesystem::fsFit($name) . '.stub';
    }

    /**
     * 组装模板
     * @param string $name
     * @param array  $data
     * @param bool   $escape
     * @return string
     */
    public static function assembleStub(string $name, array $data, bool $escape = false): string
    {
        foreach ($data as &$datum) {
            $datum = is_array($datum) ? implode(PHP_EOL, $datum) : $datum;
        }
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[]  = "{%$k%}";
            $replace[] = $v;
        }
        $stubPath    = self::getStubFilePath($name);
        $stubContent = file_get_contents($stubPath);
        $content     = str_replace($search, $replace, $stubContent);
        return $escape ? self::escape($content) : $content;
    }

    /**
     * 获取转义编码后的值
     * @param array|string $value
     * @return string
     */
    public static function escape(array|string $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    public static function tab(int $num = 1): string
    {
        return str_pad('', 4 * $num);
    }

    /**
     * 删除数据表
     */
    public static function delTable(string $table): void
    {
        $sql = 'DROP TABLE IF EXISTS `' . TableManager::tableName($table) . '`';
        Db::execute($sql);
    }

    /**
     * 根据数据表解析字段数据
     */
    public static function parseTableColumns(string $table, bool $analyseField = false): array
    {
        // 从数据库中获取表字段信息
        $sql = 'SELECT * FROM `information_schema`.`columns` '
            . 'WHERE TABLE_SCHEMA = ? AND table_name = ? '
            . 'ORDER BY ORDINAL_POSITION';

        $columns     = [];
        $tableColumn = Db::query($sql, [config('database.connections.mysql.database'), TableManager::tableName($table)]);
        foreach ($tableColumn as $item) {
            $isNullAble = $item['IS_NULLABLE'] == 'YES';
            if (str_contains($item['COLUMN_TYPE'], '(')) {
                $dataType = substr_replace($item['COLUMN_TYPE'], '', stripos($item['COLUMN_TYPE'], ')') + 1);
            } else {
                $dataType = str_replace(' unsigned', '', $item['COLUMN_TYPE']);
            }

            $column = [
                'name'          => $item['COLUMN_NAME'],
                'type'          => $item['DATA_TYPE'],
                'dataType'      => $dataType,
                'default'       => ($isNullAble && is_null($item['COLUMN_DEFAULT'])) ? 'null' : $item['COLUMN_DEFAULT'],
                'null'          => $isNullAble,
                'primaryKey'    => $item['COLUMN_KEY'] == 'PRI',
                'unsigned'      => (bool)stripos($item['COLUMN_TYPE'], 'unsigned'),
                'autoIncrement' => stripos($item['EXTRA'], 'auto_increment') !== false,
                'comment'       => $item['COLUMN_COMMENT'],
                'designType'    => self::getTableColumnsDataType($item),
                'table'         => [],
                'form'          => [],
            ];
            if ($analyseField) {
                self::analyseField($column);
            } else {
                self::handleTableColumn($column);
            }
            $columns[$item['COLUMN_NAME']] = $column;
        }
        return $columns;
    }

    /**
     * 解析到的表字段的额外处理
     */
    public static function handleTableColumn(&$column): void
    {
        // 预留
    }

    /**
     * 分析字段数据类型
     * @param array $field 字段数据
     * @return string 字段类型
     */
    public static function analyseFieldType(array $field): string
    {
        $dataType = (isset($field['dataType']) && $field['dataType']) ? $field['dataType'] : $field['type'];
        if (stripos($dataType, '(') !== false) {
            $typeName = explode('(', $dataType);
            return trim($typeName[0]);
        }
        return trim($dataType);
    }

    /**
     * 分析字段的完整数据类型定义
     * @param array $field 字段数据
     * @return string
     */
    public static function analyseFieldDataType(array $field): string
    {
        if (!empty($field['dataType'])) return $field['dataType'];

        $conciseType = self::analyseFieldType($field);
        $limit       = self::analyseFieldLimit($conciseType, $field);

        if (isset($limit['precision'])) {
            $dataType = "$conciseType({$limit['precision']}, {$limit['scale']})";
        } elseif (isset($limit['values'])) {
            $values   = implode(',', $limit['values']);
            $dataType = "$conciseType($values)";
        } else {
            $dataType = "$conciseType({$limit['limit']})";
        }
        return $dataType;
    }

    /**
     * 分析字段
     */
    public static function analyseField(&$field): void
    {
        $field['type']               = self::analyseFieldType($field);
        $field['originalDesignType'] = $field['designType'];

        // 表单项类型转换对照表
        $designTypeComparison = [
            'pk'        => 'string',
            'weigh'     => 'number',
            'timestamp' => 'datetime',
            'float'     => 'number',
        ];
        if (array_key_exists($field['designType'], $designTypeComparison)) {
            $field['designType'] = $designTypeComparison[$field['designType']];
        }

        // 是否开启了多选
        $supportMultipleComparison = ['select', 'image', 'file', 'remoteSelect'];
        if (in_array($field['designType'], $supportMultipleComparison)) {
            $multiKey = $field['designType'] == 'remoteSelect' ? 'select-multi' : $field['designType'] . '-multi';
            if (isset($field['form'][$multiKey]) && $field['form'][$multiKey]) {
                $field['designType'] = $field['designType'] . 's';
            }
        }
    }

    public static function getTableColumnsDataType($column)
    {
        if (stripos($column['COLUMN_NAME'], 'id') !== false && stripos($column['EXTRA'], 'auto_increment') !== false) {
            return 'pk';
        } elseif ($column['COLUMN_NAME'] == 'weigh') {
            return 'weigh';
        } elseif (in_array($column['COLUMN_NAME'], ['createtime', 'updatetime', 'create_time', 'update_time'])) {
            return 'timestamp';
        }
        foreach (self::$inputTypeRule as $item) {
            $typeBool       = true;
            $suffixBool     = true;
            $columnTypeBool = true;
            if (isset($item['type']) && $item['type'] && !in_array($column['DATA_TYPE'], $item['type'])) {
                $typeBool = false;
            }
            if (isset($item['suffix']) && $item['suffix']) {
                $suffixBool = self::isMatchSuffix($column['COLUMN_NAME'], $item['suffix']);
            }
            if (isset($item['column_type']) && $item['column_type'] && !in_array($column['COLUMN_TYPE'], $item['column_type'])) {
                $columnTypeBool = false;
            }
            if ($typeBool && $suffixBool && $columnTypeBool) {
                return $item['value'];
            }
        }
        return 'string';
    }

    /**
     * 判断是否符合指定后缀
     *
     * @param string       $field     字段名称
     * @param string|array $suffixArr 后缀
     * @return bool
     */
    protected static function isMatchSuffix(string $field, string|array $suffixArr): bool
    {
        $suffixArr = is_array($suffixArr) ? $suffixArr : explode(',', $suffixArr);
        foreach ($suffixArr as $v) {
            if (preg_match("/$v$/i", $field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 创建菜单
     * @throws Throwable
     */
    public static function createMenu($webViewsDir, $tableComment): void
    {
        $menuName = self::getMenuName($webViewsDir);
        if (!AdminRule::where('name', $menuName)->value('id')) {
            $pid = 0;
            foreach ($webViewsDir['path'] as $item) {
                $pMenu = AdminRule::where('name', $item)->value('id');
                if ($pMenu) {
                    $pid = $pMenu;
                    continue;
                }
                $menu = [
                    'pid'   => $pid,
                    'type'  => 'menu_dir',
                    'title' => $item,
                    'name'  => $item,
                    'path'  => $item,
                ];
                $menu = AdminRule::create($menu);
                $pid  = $menu->id;
            }

            // 建立菜单
            foreach (self::$menuChildren as &$item) {
                $item['name'] = $menuName . $item['name'];
            }
            $componentPath = str_replace(['\\', 'web/src'], ['/', '/src'], $webViewsDir['views'] . '/' . 'index.vue');
            Menu::create([
                [
                    'type'      => 'menu',
                    'title'     => $tableComment ?: $webViewsDir['originalLastName'],
                    'name'      => $menuName,
                    'path'      => $menuName,
                    'menu_type' => 'tab',
                    'component' => $componentPath,
                    'children'  => self::$menuChildren,
                ]
            ], $pid);
        }
    }

    public static function writeWebLangFile($langData, $webLangDir): void
    {
        foreach ($langData as $lang => $langDatum) {
            $langTsContent = '';
            foreach ($langDatum as $key => $item) {
                $quote         = self::getQuote($item);
                $keyStr        = self::formatObjectKey($key);
                $langTsContent .= self::tab() . $keyStr . ": $quote$item$quote,\n";
            }
            $langTsContent = "export default {\n" . $langTsContent . "}\n";
            self::writeFile(root_path() . $webLangDir[$lang] . '.ts', $langTsContent);
        }
    }

    public static function writeFile($path, $content): bool|int
    {
        $path = Filesystem::fsFit($path);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        return file_put_contents($path, $content);
    }

    public static function buildModelAppend($append): string
    {
        if (!$append) return '';
        $append = self::buildFormatSimpleArray($append);
        return "\n" . self::tab() . "// 追加属性" . "\n" . self::tab() . "protected \$append = $append;\n";
    }

    public static function buildModelFieldType(array $fieldType): string
    {
        if (!$fieldType) return '';
        $maxStrLang = 0;
        foreach ($fieldType as $key => $item) {
            $strLang    = strlen($key);
            $maxStrLang = max($strLang, $maxStrLang);
        }

        $str = '';
        foreach ($fieldType as $key => $item) {
            $str .= self::tab(2) . "'$key'" . str_pad('=>', ($maxStrLang - strlen($key) + 3), ' ', STR_PAD_LEFT) . " '$item',\n";
        }
        return "\n" . self::tab() . "// 字段类型转换" . "\n" . self::tab() . "protected \$type = [\n" . rtrim($str, "\n") . "\n" . self::tab() . "];\n";
    }

    public static function writeModelFile(string $tablePk, array $fieldsMap, array $modelData, array $modelFile): void
    {
        $modelData['pk']                 = $tablePk == 'id' ? '' : "\n" . self::tab() . "// 表主键\n" . self::tab() . 'protected $pk = ' . "'$tablePk';\n" . self::tab();
        $modelData['autoWriteTimestamp'] = array_key_exists(self::$createTimeField, $fieldsMap) || array_key_exists(self::$updateTimeField, $fieldsMap) ? 'true' : 'false';
        if ($modelData['autoWriteTimestamp'] == 'true') {
            $modelData['createTime'] = array_key_exists(self::$createTimeField, $fieldsMap) ? '' : "\n" . self::tab() . "protected \$createTime = false;";
            $modelData['updateTime'] = array_key_exists(self::$updateTimeField, $fieldsMap) ? '' : "\n" . self::tab() . "protected \$updateTime = false;";
        }
        $modelMethodList        = isset($modelData['relationMethodList']) ? array_merge($modelData['methods'], $modelData['relationMethodList']) : $modelData['methods'];
        $modelData['methods']   = $modelMethodList ? "\n" . implode("\n", $modelMethodList) : '';
        $modelData['append']    = self::buildModelAppend($modelData['append']);
        $modelData['fieldType'] = self::buildModelFieldType($modelData['fieldType']);

        // 生成雪花ID？
        if (isset($modelData['beforeInsertMixins']['snowflake'])) {
            // beforeInsert 组装
            $modelData['beforeInsert'] = Helper::assembleStub('mixins/model/beforeInsert', [
                'setSnowFlakeIdCode' => $modelData['beforeInsertMixins']['snowflake']
            ]);
        }
        if ($modelData['afterInsert'] && $modelData['beforeInsert']) {
            $modelData['afterInsert'] = "\n" . $modelData['afterInsert'];
        }

        $modelFileContent = self::assembleStub('mixins/model/model', $modelData);
        self::writeFile($modelFile['parseFile'], $modelFileContent);
    }

    public static function writeControllerFile(array $controllerData, array $controllerFile): void
    {
        if (isset($controllerData['relationVisibleFieldList']) && $controllerData['relationVisibleFieldList']) {
            $relationVisibleFields = '$res->visible([';
            foreach ($controllerData['relationVisibleFieldList'] as $cKey => $controllerDatum) {
                $relationVisibleFields .= "'$cKey' => ['" . implode("','", $controllerDatum) . "'], ";
            }
            $relationVisibleFields = rtrim($relationVisibleFields, ', ');
            $relationVisibleFields .= ']);';
            // 重写index
            $controllerData['methods']['index'] = self::assembleStub('mixins/controller/index', [
                'relationVisibleFields' => $relationVisibleFields
            ]);
            $controllerData['use']['Throwable'] = "\nuse Throwable;";
            unset($controllerData['relationVisibleFieldList']);
        }
        $controllerAttr = '';
        foreach ($controllerData['attr'] as $key => $item) {
            $attrType = '';
            if (array_key_exists($key, self::$attrType['controller'])) {
                $attrType = self::$attrType['controller'][$key];
            }
            if (is_array($item)) {
                $controllerAttr .= "\n" . self::tab() . "protected $attrType \$$key = ['" . implode("', '", $item) . "'];\n";
            } elseif ($item) {
                $controllerAttr .= "\n" . self::tab() . "protected $attrType \$$key = '$item';\n";
            }
        }
        $controllerData['attr']       = $controllerAttr;
        $controllerData['initialize'] = self::assembleStub('mixins/controller/initialize', [
            'modelNamespace' => $controllerData['modelNamespace'],
            'modelName'      => $controllerData['modelName'],
            'filterRule'     => $controllerData['filterRule'],
        ]);
        $contentFileContent           = self::assembleStub('mixins/controller/controller', $controllerData);
        self::writeFile($controllerFile['parseFile'], $contentFileContent);
    }

    public static function writeFormFile($formVueData, $webViewsDir, $fields, $webTranslate): void
    {
        $fieldHtml                = "\n";
        $formVueData['bigDialog'] = $formVueData['bigDialog'] ? "\n" . self::tab(2) . 'width="50%"' : '';
        foreach ($formVueData['formFields'] as $field) {
            $fieldHtml .= self::tab(5) . "<FormItem";
            foreach ($field as $key => $attr) {
                if (is_array($attr)) {
                    $fieldHtml .= ' ' . $key . '="' . self::getJsonFromArray($attr) . '"';
                } else {
                    $fieldHtml .= ' ' . $key . '="' . $attr . '"';
                }
            }
            $fieldHtml .= " />\n";
        }
        $formVueData['formFields'] = rtrim($fieldHtml, "\n");

        // 表单验证规则
        $formValidatorRules = [];
        foreach ($fields as $field) {
            if (isset($field['form']['validator'])) {
                foreach ($field['form']['validator'] as $item) {
                    $message = '';
                    if (isset($field['form']['validatorMsg']) && $field['form']['validatorMsg']) {
                        $message = ", message: '{$field['form']['validatorMsg']}'";
                    }
                    $formValidatorRules[$field['name']][] = "buildValidatorData({ name: '$item', title: t('$webTranslate{$field['name']}')$message })";
                }
            }
        }
        $formVueData['formItemRules'] = self::buildFormValidatorRules($formValidatorRules);
        $formVueContent               = self::assembleStub('html/form', $formVueData);
        self::writeFile(root_path() . $webViewsDir['views'] . '/' . 'popupForm.vue', $formVueContent);
    }

    public static function buildFormValidatorRules($formValidatorRules): string
    {
        $rulesHtml = "";
        foreach ($formValidatorRules as $key => $formItemRule) {
            $rulesArrHtml = '';
            foreach ($formItemRule as $item) {
                $rulesArrHtml .= $item . ', ';
            }
            $rulesHtml .= self::tab() . $key . ': [' . rtrim($rulesArrHtml, ', ') . "],\n";
        }
        return $rulesHtml ? "\n" . $rulesHtml : '';
    }

    public static function writeIndexFile($indexVueData, $webViewsDir, $controllerFile): void
    {
        $indexVueData['optButtons']            = self::buildSimpleArray($indexVueData['optButtons']);
        $indexVueData['defaultItems']          = self::getJsonFromArray($indexVueData['defaultItems']);
        $indexVueData['tableColumn']           = self::buildTableColumn($indexVueData['tableColumn']);
        $indexVueData['dblClickNotEditColumn'] = self::buildSimpleArray($indexVueData['dblClickNotEditColumn']);
        $controllerFile['path'][]              = $controllerFile['originalLastName'];
        $indexVueData['controllerUrl']         = '\'/admin/' . ($controllerFile['path'] ? implode('.', $controllerFile['path']) : '') . '/\'';
        $indexVueData['componentName']         = ($webViewsDir['path'] ? implode('/', $webViewsDir['path']) . '/' : '') . $webViewsDir['originalLastName'];
        $indexVueContent                       = self::assembleStub('html/index', $indexVueData);
        self::writeFile(root_path() . $webViewsDir['views'] . '/' . 'index.vue', $indexVueContent);
    }

    public static function buildTableColumn($tableColumnList): string
    {
        $columnJson = '';
        foreach ($tableColumnList as $column) {
            $columnJson .= self::tab(3) . '{';
            foreach ($column as $key => $item) {
                $columnJson .= self::buildTableColumnKey($key, $item);
            }
            $columnJson = rtrim($columnJson, ',');
            $columnJson .= ' }' . ",\n";
        }
        return rtrim($columnJson, "\n");
    }

    public static function buildTableColumnKey($key, $item): string
    {
        $key = self::formatObjectKey($key);
        if (is_array($item)) {
            $itemJson = ' ' . $key . ': {';
            foreach ($item as $ik => $iItem) {
                $itemJson .= self::buildTableColumnKey($ik, $iItem);
            }
            $itemJson = rtrim($itemJson, ',');
            $itemJson .= ' }';
        } else {
            if ($item === 'false' || $item === 'true') {
                $itemJson = ' ' . $key . ': ' . $item . ',';
            } elseif (in_array($key, ['label', 'width', 'buttons'], true) || str_starts_with($item, "t('") || str_starts_with($item, "t(\"")) {
                $itemJson = ' ' . $key . ': ' . $item . ',';
            } else {
                $itemJson = ' ' . $key . ': \'' . $item . '\',';
            }
        }
        return $itemJson;
    }

    public static function formatObjectKey(string $keyName): string
    {
        if (preg_match("/^[a-zA-Z_][a-zA-Z0-9_]+$/", $keyName)) {
            return $keyName;
        } else {
            $quote = self::getQuote($keyName);
            return "$quote$keyName$quote";
        }
    }

    public static function getQuote(string $value): string
    {
        return stripos($value, "'") === false ? "'" : '"';
    }

    public static function buildFormatSimpleArray($arr, int $tab = 2): string
    {
        if (!$arr) return '[]';
        $str = '[' . PHP_EOL;
        foreach ($arr as $item) {
            if ($item == 'undefined' || $item == 'false' || is_numeric($item)) {
                $str .= self::tab($tab) . $item . ',' . PHP_EOL;
            } else {
                $quote = self::getQuote($item);
                $str   .= self::tab($tab) . "$quote$item$quote," . PHP_EOL;
            }
        }
        return $str . self::tab($tab - 1) . ']';
    }

    public static function buildSimpleArray($arr): string
    {
        if (!$arr) return '[]';
        $str = '';
        foreach ($arr as $item) {
            if ($item == 'undefined' || $item == 'false' || is_numeric($item)) {
                $str .= $item . ', ';
            } else {
                $quote = self::getQuote($item);
                $str   .= "$quote$item$quote, ";
            }
        }
        return '[' . rtrim($str, ", ") . ']';
    }

    public static function buildDefaultOrder(string $field, string $type): string
    {
        if ($field && $type) {
            $defaultOrderStub = [
                'prop'  => $field,
                'order' => $type,
            ];
            $defaultOrderStub = self::getJsonFromArray($defaultOrderStub);
            if ($defaultOrderStub) {
                return "\n" . self::tab(2) . "defaultOrder: " . $defaultOrderStub . ',';
            }
        }
        return '';
    }

    public static function getJsonFromArray($arr)
    {
        if (is_array($arr)) {
            $jsonStr = '';
            foreach ($arr as $key => $item) {
                $keyStr = ' ' . self::formatObjectKey($key) . ': ';
                if (is_array($item)) {
                    $jsonStr .= $keyStr . self::getJsonFromArray($item) . ',';
                } elseif ($item === 'false' || $item === 'true') {
                    $jsonStr .= $keyStr . ($item === 'false' ? 'false' : 'true') . ',';
                } elseif ($item === null) {
                    $jsonStr .= $keyStr . 'null,';
                } elseif (str_starts_with($item, "t('") || str_starts_with($item, "t(\"") || $item == '[]' || in_array(gettype($item), ['integer', 'double'])) {
                    $jsonStr .= $keyStr . $item . ',';
                } elseif (isset($item[0]) && $item[0] == '[' && str_ends_with($item, ']')) {
                    $jsonStr .= $keyStr . $item . ',';
                } else {
                    $quote   = self::getQuote($item);
                    $jsonStr .= $keyStr . "$quote$item$quote,";
                }
            }
            return $jsonStr ? '{' . rtrim($jsonStr, ',') . ' }' : '{}';
        } else {
            return $arr;
        }
    }

}