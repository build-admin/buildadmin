<?php

namespace app\admin\command\Crud\library;

class Stub
{
    protected static $instance;
    protected        $stubList = [];
    protected        $options  = [
        // 转义Html
        'escapeHtml' => false
    ];

    // 生成FormItem组件的输入框类型
    protected static $formItemType = [
        'string',
        'number',
        'radio',
        'checkbox',
        'switch',
        'textarea',
        'array',
        'datetime',
        'year',
        'date',
        'time',
        'select',
        'selects',
        'remoteSelect',
        'remoteSelects',
        'editor',
        'city',
        'image',
        'images',
        'file',
        'files',
        'icon',
    ];

    /**
     * 获取单例
     * @param array $options
     * @return static
     */
    public static function instance(array $options = []): Stub
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * 获取替换后的数据.
     * @param string $name
     * @param array  $data
     * @return string
     */
    public function getReplacedStub(string $name, array $data): string
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[]  = "{%{$k}%}";
            $replace[] = $v;
        }
        $stubname = $this->getStub($name);
        if (isset($this->stubList[$stubname])) {
            $stub = $this->stubList[$stubname];
        } else {
            $this->stubList[$stubname] = $stub = file_get_contents($stubname);
        }
        $content = str_replace($search, $replace, $stub);
        return $this->escape($content);
    }

    /**
     * 获取基础模板
     * @param string $name
     * @return string
     */
    public function getStub(string $name): string
    {
        return app_path() . 'admin' . DIRECTORY_SEPARATOR . 'command' . DIRECTORY_SEPARATOR . 'Crud' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $name . '.stub';
    }

    public static function buildModelFieldType($modelFieldType)
    {
        if (!$modelFieldType) return '';
        $maxStrLang = 0;
        foreach ($modelFieldType as $key => $item) {
            $strLang    = strlen($key);
            $maxStrLang = max($strLang, $maxStrLang);
        }

        $str = "";
        foreach ($modelFieldType as $key => $item) {
            $str .= self::tab(2) . "'{$key}'" . str_pad('=>', ($maxStrLang - strlen($key) + 3), ' ', STR_PAD_LEFT) . " '{$item}',\n";
        }
        return "\n" . self::tab() . "protected \$type = [\n" . rtrim($str, "\n") . "\n" . self::tab() . "];";
    }

    public static function buildTableColumnKey($key, $item)
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
            } elseif (in_array($key, ['label', 'width', 'buttons']) || strpos($item, "t('") === 0 || strpos($item, "t(\"") === 0) {
                $itemJson = ' ' . $key . ': ' . $item . ',';
            } else {
                $itemJson = ' ' . $key . ': \'' . $item . '\',';
            }
        }
        return $itemJson;
    }

    public static function buildTableColumn(&$tableColumnList)
    {
        $columnJson = '';
        foreach ($tableColumnList as $column) {
            $columnJson .= '            {';
            foreach ($column as $key => $item) {
                $columnJson .= self::buildTableColumnKey($key, $item);
            }
            $columnJson = rtrim($columnJson, ',');
            $columnJson .= ' }' . ",\n";
        }
        $tableColumnList = rtrim($columnJson, "\n");
    }

    public static function buildFormField(&$formFieldList)
    {
        $fieldHtml = "\n";
        foreach ($formFieldList as $item) {
            if (in_array($item['type'], self::$formItemType)) {
                // FormItem
                $fieldHtml .= self::tab(4) . "<FormItem";
                foreach ($item as $key => $attr) {
                    if (is_array($attr)) {
                        $fieldHtml .= ' ' . $key . '="' . self::getJsonFromArray($attr) . '"';
                    } else {
                        $fieldHtml .= ' ' . $key . '="' . $attr . '"';
                    }
                }
                $fieldHtml .= " />\n";
            }
        }
        $formFieldList = rtrim($fieldHtml, "\n");
    }

    public static function buildFormRules(&$formItemRules)
    {
        $rulesHtml = "";
        foreach ($formItemRules as $key => $formItemRule) {
            $rulesArrHtml = '';
            foreach ($formItemRule as $item) {
                $rulesArrHtml .= $item . ', ';
            }
            $rulesHtml .= self::tab() . $key . ': [' . rtrim($rulesArrHtml, ', ') . "],\n";
        }
        $formItemRules = $rulesHtml ? "\n" . $rulesHtml : '';
    }

    public static function getJsonFromArray($array)
    {
        if (is_array($array)) {
            $jsonStr = '';
            foreach ($array as $key => $item) {
                $keyStr = strpos($key, "-") === false ? ' ' . $key . ': ' : ' \'' . $key . '\': ';
                if (is_array($item)) {
                    $jsonStr .= $keyStr . self::getJsonFromArray($item) . ',';
                } elseif ($item === 'false' || $item === 'true') {
                    $jsonStr .= $keyStr . ($item === 'false' ? 'false' : 'true') . ',';
                } elseif (strpos($item, "t('") === 0 || strpos($item, "t(\"") === 0) {
                    $jsonStr .= $keyStr . $item . ',';
                } elseif (($key == 'remote-url' && strpos($item, "+") !== false) || $key == 'rows') {
                    $jsonStr .= $keyStr . $item . ',';
                } else {
                    $jsonStr .= $keyStr . '\'' . $item . '\',';
                }
            }
            return '{' . rtrim($jsonStr, ',') . ' }';
        } else {
            return $array;
        }
    }

    public static function buildDblClickNotEditColumn(&$dblClickNotEditColumn)
    {
        $columnJson = '';
        foreach ($dblClickNotEditColumn as $item) {
            if ($item === 'undefined') {
                $columnJson .= $item . ', ';
            } else {
                $columnJson .= '\'' . $item . '\',';
            }
        }
        $dblClickNotEditColumn = '[' . rtrim($columnJson, ',') . ']';
    }

    public static function buildDefaultOrder($defaultOrder)
    {
        if (isset($defaultOrder[0]) && isset($defaultOrder[1])) {
            $defaultOrderStub = [
                'prop'  => $defaultOrder[0],
                'order' => $defaultOrder[1],
            ];
            $defaultOrderStub = self::getJsonFromArray($defaultOrderStub);
            if ($defaultOrderStub) {
                return "\n" . self::tab(2) . "defaultOrder: " . $defaultOrderStub . ',';
            }
        }
        return '';
    }

    public static function writeToFile($pathname, $content)
    {
        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }

    public static function writeWebLangFile($langList, $webLangEnFile, $webLangZhCnFile)
    {
        // 英文语言包写入
        if (isset($langList['en']) && $langList['en']) {
            $enLangTs = '';
            foreach ($langList['en'] as $key => $item) {
                $enLangTs .= self::tab() . '"' . $key . '": "' . $item . "\",\n";
            }
            $enLangTs = "export default {\n" . $enLangTs . "}";
            self::writeToFile($webLangEnFile, $enLangTs);
        }
        // 中文语言包写入
        if (isset($langList['zh-cn']) && $langList['zh-cn']) {
            $zhCnLangTs = '';
            foreach ($langList['zh-cn'] as $key => $item) {
                $zhCnLangTs .= self::tab() . '"' . $key . '": "' . $item . "\",\n";
            }
            $zhCnLangTs = "export default {\n" . $zhCnLangTs . "}";
            self::writeToFile($webLangZhCnFile, $zhCnLangTs);
        }
    }

    /**
     * 设置是否转义
     * @param boolean $escape
     */
    public function setEscapeHtml(bool $escape)
    {
        $this->options['escapeHtml'] = $escape;
    }

    /**
     * 获取转义编码后的值
     * @param string|array $value
     * @return string
     */
    public function escape($value): string
    {
        if (!$this->options['escapeHtml']) {
            return $value;
        }
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    public static function tab(int $num = 1): string
    {
        return str_pad('', 4 * $num);
    }
}