<?php

namespace app\admin\library\crud;

use think\Exception;
use think\facade\Db;
use app\common\library\Menu;
use app\admin\model\MenuRule;
use app\admin\model\CrudLog;

class Helper
{
    /**
     * 内部保留词
     */
    protected static $reservedKeywords = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield'
    ];

    /**
     * 预设控制器和模型文件位置
     */
    protected static $parseNamePresets = [
        'controller' => [
            'user'        => ['user', 'user'],
            'admin'       => ['auth', 'admin'],
            'admin_group' => ['auth', 'group'],
            'attachment'  => ['routine', 'attachment'],
            'menu_rule'   => ['auth', 'menu'],
        ],
        'model'      => [],
        'validate'   => [],
    ];

    /**
     * 子级菜单数组(权限节点)
     * @var string
     */
    protected static $menuChildren = [
        ['type' => 'button', 'title' => '查看', 'name' => '/index', 'status' => '1'],
        ['type' => 'button', 'title' => '添加', 'name' => '/add', 'status' => '1'],
        ['type' => 'button', 'title' => '编辑', 'name' => '/edit', 'status' => '1'],
        ['type' => 'button', 'title' => '删除', 'name' => '/del', 'status' => '1'],
        ['type' => 'button', 'title' => '快速排序', 'name' => '/sortable', 'status' => '1'],
    ];

    /**
     * 输入框类型的识别规则
     */
    protected static $inputTypeRule = [
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
    ];

    /**
     * 预设WEB端文件位置
     */
    protected static $parseWebDirPresets = [
        'lang'  => [],
        'views' => [
            'user'        => ['user', 'user'],
            'admin'       => ['auth', 'admin'],
            'admin_group' => ['auth', 'group'],
            'attachment'  => ['routine', 'attachment'],
            'menu_rule'   => ['auth', 'menu'],
        ],
    ];

    /**
     * 添加时间字段
     * @var string
     */
    protected static $createTimeField = 'create_time';

    /**
     * 更新时间字段
     * @var string
     */
    protected static $updateTimeField = 'update_time';

    public static function getDictData(&$dict, $field, $lang, $translationPrefix = ''): array
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

    public static function recordCrudStatus(array $data)
    {
        if (isset($data['id'])) {
            return CrudLog::where('id', $data['id'])
                ->update([
                    'status' => $data['status'],
                ]);
        }
        $log = CrudLog::create([
            'table_name' => $data['table']['name'],
            'table'      => $data['table'],
            'fields'     => $data['fields'],
            'status'     => $data['status'],
        ]);
        return $log->id;
    }

    public static function createTable($name, $comment, $fields): array
    {
        $fieldType = [
            'variableLength' => ['blob', 'date', 'enum', 'geometry', 'geometrycollection', 'json', 'linestring', 'longblob', 'longtext', 'mediumblob', 'mediumtext', 'multilinestring', 'multipoint', 'multipolygon', 'point', 'polygon', 'set', 'text', 'tinyblob', 'tinytext', 'year'],
            'fixedLength'    => ['int', 'bigint', 'binary', 'bit', 'char', 'datetime', 'mediumint', 'smallint', 'time', 'timestamp', 'tinyint', 'varbinary', 'varchar'],
            'decimal'        => ['decimal', 'double', 'float'],
        ];
        $name      = self::getTableName($name);
        $sql       = "CREATE TABLE IF NOT EXISTS `$name` (" . PHP_EOL;
        $pk        = '';
        foreach ($fields as $field) {
            if (!isset($field['dataType']) || !$field['dataType']) {
                if (!$field['type']) {
                    continue;
                }
                if (in_array($field['type'], $fieldType['fixedLength'])) {
                    $field['dataType'] = "{$field['type']}({$field['length']})";
                } elseif (in_array($field['type'], $fieldType['decimal'])) {
                    $field['dataType'] = "{$field['type']}({$field['length']},{$field['precision']})";
                } elseif (in_array($field['type'], $fieldType['variableLength'])) {
                    $field['dataType'] = $field['type'];
                } else {
                    $field['dataType'] = $field['precision'] ? "{$field['type']}({$field['length']},{$field['precision']})" : "{$field['type']}({$field['length']})";
                }
            }
            $unsigned      = $field['unsigned'] ? ' UNSIGNED' : '';
            $null          = $field['null'] ? ' NULL' : ' NOT NULL';
            $autoIncrement = $field['autoIncrement'] ? ' AUTO_INCREMENT' : '';
            $default       = '';
            if (strtolower($field['default']) == 'null') {
                $default = ' DEFAULT NULL';
            } elseif ($field['default'] == '0') {
                $default = " DEFAULT '0'";
            } elseif ($field['default'] == 'empty string') {
                $default = " DEFAULT ''";
            } elseif ($field['default']) {
                $default = " DEFAULT '{$field['default']}'";
            }
            $fieldComment = $field['comment'] ? " COMMENT '{$field['comment']}'" : '';
            $sql          .= "`{$field['name']}` {$field['dataType']}$unsigned$null$autoIncrement$default$fieldComment ," . PHP_EOL;
            if ($field['primaryKey']) {
                $pk = $field['name'];
            }
        }
        $sql .= "PRIMARY KEY (`$pk`)" . PHP_EOL . ") ";
        $sql .= "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='$comment'";
        Db::execute($sql);
        return [$pk];
    }

    public static function parseNameData($app, $table, $name, $type, $value = ''): array
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
            $originalLastName = array_pop($pathArr);
            $lastName         = ucfirst($originalLastName);
        } else {
            if (!$name) {
                if (isset(self::$parseNamePresets[$type]) && array_key_exists($table, self::$parseNamePresets[$type])) {
                    $pathArr          = self::$parseNamePresets[$type][$table];
                    $originalLastName = array_pop($pathArr);
                    $lastName         = ucfirst($originalLastName);
                } else {
                    $originalLastName = $lastName = parse_name($table, 1);
                }
            } else {
                $name             = str_replace(['.', '/', '\\', '_'], '/', $name);
                $pathArr          = explode('/', $name);
                $originalLastName = array_pop($pathArr);
                $lastName         = ucfirst($originalLastName);
            }
        }

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
            'parseFile'        => path_transform($parseFile),
            'rootFileName'     => path_transform($rootFileName),
        ];
    }

    public static function parseWebDirNameData($table, $name, $type, $value = ''): array
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
            $originalLastName = array_pop($pathArr);
            $lastName         = lcfirst($originalLastName);
        } else {
            if (!$name) {
                if (array_key_exists($table, self::$parseWebDirPresets[$type])) {
                    $pathArr          = self::$parseWebDirPresets[$type][$table];
                    $originalLastName = array_pop($pathArr);
                    $lastName         = lcfirst($originalLastName);
                } else {
                    $originalLastName = $lastName = parse_name($table, 1, false);
                }
            } else {
                $name             = str_replace(['.', '/', '\\', '_'], '/', $name);
                $pathArr          = explode('/', $name);
                $originalLastName = array_pop($pathArr);
                $lastName         = lcfirst($originalLastName);
            }
        }

        $webDir['path']             = $pathArr;
        $webDir['lastName']         = $lastName;
        $webDir['originalLastName'] = $originalLastName;
        if ($type == 'views') {
            $webDir['views'] = "web/src/views/backend" . ($pathArr ? '/' . implode('/', $pathArr) : '') . "/$lastName";
        } elseif ($type == 'lang') {
            $webDir['lang'] = array_merge($pathArr, [$lastName]);
            $langDir        = ['en', 'zh-cn'];
            foreach ($langDir as $item) {
                $webDir[$item] = "web/src/lang/pages/$item" . ($pathArr ? '/' . implode('/', $pathArr) : '') . "/$lastName";
            }
        }
        foreach ($webDir as &$item) {
            if (is_string($item)) $item = path_transform($item);
        }
        return $webDir;
    }

    public static function getTableName(string $table, $fullName = true): string
    {
        $tablePrefix = config('database.connections.mysql.prefix');
        $pattern     = '/^' . $tablePrefix . '/i';
        return ($fullName ? $tablePrefix : '') . (preg_replace($pattern, '', $table));
    }

    /**
     * 获取菜单name、path
     * @param array $webDir
     * @return string
     */
    public static function getMenuName(array $webDir): string
    {
        return ($webDir['path'] ? implode('.', $webDir['path']) . '/' : '') . $webDir['originalLastName'];
    }

    /**
     * 获取基础模板文件路径
     * @param string $name
     * @return string
     */
    public static function getStubFilePath(string $name): string
    {
        return app_path() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'crud' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . path_transform($name) . '.stub';
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
     * @param string|array $value
     * @return string
     */
    public static function escape($value): string
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
    public static function delTable(string $table)
    {
        $sql = 'DROP TABLE IF EXISTS `' . self::getTableName($table) . '`';
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
        $tableColumn = Db::query($sql, [config('database.connections.mysql.database'), self::getTableName($table)]);
        foreach ($tableColumn as $item) {
            $columns[$item['COLUMN_NAME']] = [
                'name'          => $item['COLUMN_NAME'],
                'type'          => $item['DATA_TYPE'],
                'dataType'      => stripos($item['COLUMN_TYPE'], '(') !== false ? substr_replace($item['COLUMN_TYPE'], '', stripos($item['COLUMN_TYPE'], ')') + 1) : $item['COLUMN_TYPE'],
                'default'       => $item['COLUMN_DEFAULT'],
                'null'          => $item['IS_NULLABLE'] == 'YES',
                'primaryKey'    => $item['COLUMN_KEY'] == 'PRI',
                'unsigned'      => (bool)stripos($item['COLUMN_TYPE'], 'unsigned'),
                'autoIncrement' => stripos($item['EXTRA'], 'auto_increment') !== false,
                'comment'       => $item['COLUMN_COMMENT'],
                'designType'    => self::getTableColumnsDataType($item),
                'table'         => [],
                'form'          => [],
            ];
            if ($analyseField) {
                self::analyseField($columns[$item['COLUMN_NAME']]);
            }
        }
        return $columns;
    }

    /**
     * 分析字段
     */
    public static function analyseField(&$field)
    {
        $field['dataType'] = (isset($field['dataType']) && $field['dataType']) ? $field['dataType'] : $field['type'];
        if (stripos($field['dataType'], '(') !== false) {
            $typeName      = explode('(', $field['dataType']);
            $field['type'] = trim($typeName[0]);
        }

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
     * @param string $field     字段名称
     * @param mixed  $suffixArr 后缀
     * @return bool
     */
    protected static function isMatchSuffix(string $field, $suffixArr): bool
    {
        $suffixArr = is_array($suffixArr) ? $suffixArr : explode(',', $suffixArr);
        foreach ($suffixArr as $v) {
            if (preg_match("/$v$/i", $field)) {
                return true;
            }
        }
        return false;
    }

    public static function createMenu($webViewsDir, $tableComment)
    {
        $menuName = self::getMenuName($webViewsDir);
        if (!MenuRule::where('name', $menuName)->value('id')) {
            $pid = 0;
            foreach ($webViewsDir['path'] as $item) {
                $pMenu = MenuRule::where('name', $item)->value('id');
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
                $menu = MenuRule::create($menu);
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

    public static function writeWebLangFile($langData, $webLangDir)
    {
        foreach ($langData as $lang => $langDatum) {
            $langTsContent = '';
            foreach ($langDatum as $key => $item) {
                $quote = stripos($item, "'") === false ? "'" : '"';
                if (preg_match("/^[a-zA-Z][a-zA-Z0-9_]+$/", $key)) {
                    $langTsContent .= self::tab() . $key . ": $quote$item$quote,\n";
                } else {
                    $keyQuote      = stripos($key, "'") === false ? "'" : '"';
                    $langTsContent .= self::tab() . "$keyQuote$key$keyQuote: $quote$item$quote,\n";
                }
            }
            $langTsContent = "export default {\n" . $langTsContent . "}\n";
            self::writeFile(root_path() . $webLangDir[$lang] . '.ts', $langTsContent);
        }
    }

    public static function writeFile($path, $content)
    {
        $path = path_transform($path);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        return file_put_contents($path, $content);
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

    public static function writeModelFile(string $tablePk, array $fieldsMap, array $modelData, array $modelFile)
    {
        $modelData['pk']                 = $tablePk == 'id' ? '' : "\n" . self::tab() . "// 表主键\n" . self::tab() . 'protected $pk = ' . "'$tablePk';\n" . self::tab();
        $modelData['autoWriteTimestamp'] = array_key_exists(self::$createTimeField, $fieldsMap) || array_key_exists(self::$updateTimeField, $fieldsMap) ? 'true' : 'false';
        if ($modelData['autoWriteTimestamp'] == 'true') {
            $modelData['createTime'] = array_key_exists(self::$createTimeField, $fieldsMap) ? '' : "\n" . self::tab() . "protected \$createTime = false;";
            $modelData['updateTime'] = array_key_exists(self::$updateTimeField, $fieldsMap) ? '' : "\n" . self::tab() . "protected \$updateTime = false;";
        }
        $modelMethodList        = isset($modelData['relationMethodList']) ? array_merge($modelData['methods'], $modelData['relationMethodList']) : $modelData['methods'];
        $modelData['methods']   = $modelMethodList ? "\n" . implode("\n", $modelMethodList) : '';
        $modelData['fieldType'] = self::buildModelFieldType($modelData['fieldType']);
        $modelFileContent       = self::assembleStub('mixins/model/model', $modelData);
        self::writeFile($modelFile['parseFile'], $modelFileContent);
    }

    public static function writeControllerFile(array $controllerData, array $controllerFile)
    {
        if (isset($controllerData['relationVisibleFieldList']) && $controllerData['relationVisibleFieldList']) {
            $relationVisibleFields = '$res->visible([';
            foreach ($controllerData['relationVisibleFieldList'] as $cKey => $controllerDatum) {
                $relationVisibleFields .= "'$cKey' => ['" . implode("','", $controllerDatum) . "'],";
            }
            $relationVisibleFields = rtrim($relationVisibleFields, ',');
            $relationVisibleFields .= ']);';
            // 重写index
            $controllerData['methods']['index'] = self::assembleStub('mixins/controller/index', [
                'relationVisibleFields' => $relationVisibleFields
            ]);
            unset($controllerData['relationVisibleFieldList']);
        }
        $controllerAttr = '';
        foreach ($controllerData['attr'] as $key => $item) {
            if (is_array($item)) {
                $controllerAttr .= "\n" . self::tab() . "protected \$$key = ['" . implode("', '", $item) . "'];\n";
            } elseif ($item) {
                $controllerAttr .= "\n" . self::tab() . "protected \$$key = '$item';\n";
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

    public static function writeFormFile($formVueData, $webViewsDir, $fields, $webTranslate)
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

    public static function writeIndexFile($indexVueData, $webViewsDir, $controllerFile)
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
        if (is_array($item)) {
            $itemJson = ' ' . $key . ': {';
            foreach ($item as $ik => $iitem) {
                $itemJson .= self::buildTableColumnKey($ik, $iitem);
            }
            $itemJson = rtrim($itemJson, ',');
            $itemJson .= ' }';
        } else {
            if ($item === 'false') {
                $itemJson = ' ' . $key . ': false,';
            } elseif (in_array($key, ['label', 'width', 'buttons'], true) || strpos($item, "t('") === 0 || strpos($item, "t(\"") === 0) {
                $itemJson = ' ' . $key . ': ' . $item . ',';
            } else {
                $itemJson = ' ' . $key . ': \'' . $item . '\',';
            }
        }
        return $itemJson;
    }

    public static function buildSimpleArray($arr): string
    {
        $json = '';
        foreach ($arr as $item) {
            if ($item == 'undefined' || $item == 'false' || is_numeric($item)) {
                $json .= $item . ', ';
            } else {
                $json .= '\'' . $item . '\', ';
            }
        }
        return '[' . rtrim($json, ", ") . ']';
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
                $keyStr = preg_match("/^[a-zA-Z][a-zA-Z0-9_]+$/", $key) ? ' ' . $key . ': ' : ' \'' . $key . '\': ';
                if (is_array($item)) {
                    $jsonStr .= $keyStr . self::getJsonFromArray($item) . ',';
                } elseif ($item === 'false' || $item === 'true') {
                    $jsonStr .= $keyStr . ($item === 'false' ? 'false' : 'true') . ',';
                } elseif (strpos($item, "t('") === 0 || strpos($item, "t(\"") === 0 || $item == '[]' || in_array($key, ['step', 'rows'], true)) {
                    $jsonStr .= $keyStr . $item . ',';
                } elseif ($item === null) {
                    $jsonStr .= $keyStr . 'null,';
                } elseif ($item[0] == '[' && substr($item, -1, 1) == ']') {
                    $jsonStr .= $keyStr . $item . ',';
                } else {
                    $quote   = stripos($item, "'") === false ? "'" : '"';
                    $jsonStr .= $keyStr . "$quote$item$quote,";
                }
            }
            return $jsonStr ? '{' . rtrim($jsonStr, ',') . ' }' : '{}';
        } else {
            return $arr;
        }
    }

}