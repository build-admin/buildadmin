<?php

namespace app\admin\controller\crud;

use Throwable;
use ba\Exception;
use ba\Filesystem;
use think\facade\Db;
use ba\TableManager;
use app\admin\model\CrudLog;
use app\common\library\Menu;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\admin\library\crud\Helper;

class Crud extends Backend
{
    /**
     * 模型文件数据
     * @var array
     */
    protected array $modelData = [];

    /**
     * 控制器文件数据
     * @var array
     */
    protected array $controllerData = [];

    /**
     * index.vue文件数据
     * @var array
     */
    protected array $indexVueData = [];

    /**
     * form.vue文件数据
     * @var array
     */
    protected array $formVueData = [];

    /**
     * 语言翻译前缀
     * @var string
     */
    protected string $webTranslate = '';

    /**
     * 语言包数据
     * @var array
     */
    protected array $langTsData = [];

    /**
     * 当designType为以下值时:
     * 1. 出入库字符串到数组转换
     * 2. 默认值转数组
     * @var array
     */
    protected array $dtStringToArray = ['checkbox', 'selects', 'remoteSelects', 'city', 'images', 'files'];

    protected array $noNeedPermission = ['logStart', 'getFileData', 'parseFieldData', 'generateCheck', 'databaseList'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 开始生成
     * @throws Throwable
     */
    public function generate()
    {
        $type   = $this->request->post('type', '');
        $table  = $this->request->post('table', []);
        $fields = $this->request->post('fields', [], 'clean_xss,htmlspecialchars_decode_improve');

        if (!$table || !$fields || !isset($table['name']) || !$table['name']) {
            $this->error(__('Parameter error'));
        }

        try {
            // 记录日志
            $crudLogId = Helper::recordCrudStatus([
                'table'  => $table,
                'fields' => $fields,
                'status' => 'start',
            ]);

            if ($type == 'create' || $table['rebuild'] == 'Yes') {
                // 数据表存在则删除
                Helper::delTable($table['name']);
            }

            // 处理表设计
            [$tablePk] = Helper::handleTableDesign($table, $fields);

            // 表名称
            $tableName = TableManager::tableName($table['name'], false);

            // 表注释
            $tableComment = mb_substr($table['comment'], -1) == '表' ? mb_substr($table['comment'], 0, -1) . '管理' : $table['comment'];

            // 生成文件信息解析
            $modelFile      = Helper::parseNameData($table['isCommonModel'] ? 'common' : 'admin', $tableName, 'model', $table['modelFile']);
            $validateFile   = Helper::parseNameData('admin', $tableName, 'validate', $table['validateFile']);
            $controllerFile = Helper::parseNameData('admin', $tableName, 'controller', $table['controllerFile']);
            $webViewsDir    = Helper::parseWebDirNameData($tableName, 'views', $table['webViewsDir']);
            $webLangDir     = Helper::parseWebDirNameData($tableName, 'lang', $table['webViewsDir']);

            // 语言翻译前缀
            $this->webTranslate = implode('.', $webLangDir['lang']) . '.';

            // 快速搜索字段
            if (!in_array($tablePk, $table['quickSearchField'])) {
                $table['quickSearchField'][] = $tablePk;
            }
            $quickSearchFieldZhCnTitle = [];

            // 模型数据
            $this->modelData['append']             = [];
            $this->modelData['methods']            = [];
            $this->modelData['fieldType']          = [];
            $this->modelData['createTime']         = '';
            $this->modelData['updateTime']         = '';
            $this->modelData['beforeInsertMixins'] = [];
            $this->modelData['beforeInsert']       = '';
            $this->modelData['afterInsert']        = '';
            $this->modelData['name']               = $tableName;
            $this->modelData['className']          = $modelFile['lastName'];
            $this->modelData['namespace']          = $modelFile['namespace'];
            $this->modelData['relationMethodList'] = [];

            // 控制器数据
            $this->controllerData['use']            = [];
            $this->controllerData['attr']           = [];
            $this->controllerData['methods']        = [];
            $this->controllerData['filterRule']     = '';
            $this->controllerData['className']      = $controllerFile['lastName'];
            $this->controllerData['namespace']      = $controllerFile['namespace'];
            $this->controllerData['tableComment']   = $tableComment;
            $this->controllerData['modelName']      = $modelFile['lastName'];
            $this->controllerData['modelNamespace'] = $modelFile['namespace'];

            // index.vue数据
            $this->indexVueData['enableDragSort']        = false;
            $this->indexVueData['defaultItems']          = [];
            $this->indexVueData['tableColumn']           = [
                [
                    'type'     => 'selection',
                    'align'    => 'center',
                    'operator' => 'false',
                ],
            ];
            $this->indexVueData['dblClickNotEditColumn'] = ['undefined'];
            $this->indexVueData['optButtons']            = ['edit', 'delete'];
            $this->indexVueData['defaultOrder']          = '';

            // form.vue数据
            $this->formVueData['bigDialog']  = 'false';
            $this->formVueData['formFields'] = [];

            // 语言包数据
            $this->langTsData = [
                'en'    => [],
                'zh-cn' => [],
            ];

            // 简化的字段数据
            $fieldsMap = [];

            foreach ($fields as $key => $field) {

                $fieldsMap[$field['name']] = $field['designType'];

                // 分析字段
                Helper::analyseField($field);

                Helper::getDictData($this->langTsData['en'], $field, 'en');
                Helper::getDictData($this->langTsData['zh-cn'], $field, 'zh-cn');

                // 快速搜索字段
                if (in_array($field['name'], $table['quickSearchField'])) {
                    $quickSearchFieldZhCnTitle[] = $this->langTsData['zh-cn'][$field['name']] ?? $field['name'];
                }

                // 不允许双击编辑的字段
                if ($field['designType'] == 'switch') {
                    $this->indexVueData['dblClickNotEditColumn'][] = $field['name'];
                }

                // 列字典数据
                $columnDict = $this->getColumnDict($field);

                // 表单项
                if (in_array($field['name'], $table['formFields'])) {
                    $this->formVueData['formFields'][] = $this->getFormField($field, $columnDict);
                }

                // 表格列
                if (in_array($field['name'], $table['columnFields'])) {
                    $this->indexVueData['tableColumn'][] = $this->getTableColumn($field, $columnDict);
                }

                // 关联表数据解析
                if (in_array($field['designType'], ['remoteSelect', 'remoteSelects'])) {
                    $this->parseJoinData($field);
                }

                // 模型方法
                $this->parseModelMethods($field, $this->modelData);

                // 控制器/模型等文件的一些杂项属性解析
                $this->parseSundryData($field, $table);

                if (!in_array($field['name'], $table['formFields'])) {
                    $this->controllerData['attr']['preExcludeFields'][] = $field['name'];
                }
            }

            // 快速搜索提示
            $this->langTsData['en']['quick Search Fields']    = implode(',', $table['quickSearchField']);
            $this->langTsData['zh-cn']['quick Search Fields'] = implode('、', $quickSearchFieldZhCnTitle);
            $this->controllerData['attr']['quickSearchField'] = $table['quickSearchField'];

            // 开启字段排序
            $weighKey = array_search('weigh', $fieldsMap);
            if ($weighKey !== false) {
                $this->indexVueData['enableDragSort'] = true;
                $this->modelData['afterInsert']       = Helper::assembleStub('mixins/model/afterInsert', [
                    'field' => $weighKey
                ]);
            }

            // 表格的操作列
            $this->indexVueData['tableColumn'][] = [
                'label'    => "t('Operate')",
                'align'    => 'center',
                'width'    => $this->indexVueData['enableDragSort'] ? 140 : 100,
                'render'   => 'buttons',
                'buttons'  => 'optButtons',
                'operator' => 'false',
            ];
            if ($this->indexVueData['enableDragSort']) {
                array_unshift($this->indexVueData['optButtons'], 'weigh-sort');
            }

            // 写入语言包代码
            Helper::writeWebLangFile($this->langTsData, $webLangDir);

            // 写入模型代码
            Helper::writeModelFile($tablePk, $fieldsMap, $this->modelData, $modelFile);

            // 写入控制器代码
            Helper::writeControllerFile($this->controllerData, $controllerFile);

            // 写入验证器代码
            $validateContent = Helper::assembleStub('mixins/validate/validate', [
                'namespace' => $validateFile['namespace'],
                'className' => $validateFile['lastName'],
            ]);
            Helper::writeFile($validateFile['parseFile'], $validateContent);

            // 写入index.vue代码
            $this->indexVueData['tablePk']      = $tablePk;
            $this->indexVueData['webTranslate'] = $this->webTranslate;
            Helper::writeIndexFile($this->indexVueData, $webViewsDir, $controllerFile);

            // 写入form.vue代码
            Helper::writeFormFile($this->formVueData, $webViewsDir, $fields, $this->webTranslate);

            // 生成菜单
            Helper::createMenu($webViewsDir, $tableComment);

            Helper::recordCrudStatus([
                'id'     => $crudLogId,
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            Helper::recordCrudStatus([
                'id'     => $crudLogId ?? 0,
                'status' => 'error',
            ]);
            $this->error($e->getMessage());
        } catch (Throwable $e) {
            Helper::recordCrudStatus([
                'id'     => $crudLogId ?? 0,
                'status' => 'error',
            ]);
            if (env('app_debug', false)) throw $e;
            $this->error($e->getMessage());
        }

        $this->success();
    }

    /**
     * 从log开始
     * @throws Throwable
     */
    public function logStart(): void
    {
        $id   = $this->request->post('id');
        $info = CrudLog::find($id)->toArray();
        if (!$info) {
            $this->error(__('Record not found'));
        }

        // 数据表是否有数据
        $adapter = TableManager::adapter();
        if ($adapter->hasTable($info['table']['name'])) {
            $info['table']['empty'] = Db::name($info['table']['name'])->limit(1)->select()->isEmpty();
        } else {
            $info['table']['empty'] = true;
        }

        AdminLog::setTitle(__('Log start'));

        $this->success('', [
            'table'  => $info['table'],
            'fields' => $info['fields'],
        ]);
    }

    /**
     * 删除CRUD记录和生成的文件
     * @throws Throwable
     */
    public function delete()
    {
        $id   = $this->request->post('id');
        $info = CrudLog::find($id)->toArray();
        if (!$info) {
            $this->error(__('Record not found'));
        }
        $webLangDir = Helper::parseWebDirNameData($info['table']['name'], 'lang', $info['table']['webViewsDir']);
        $files      = [
            $webLangDir['en'] . '.ts',
            $webLangDir['zh-cn'] . '.ts',
            $info['table']['webViewsDir'] . '/' . 'index.vue',
            $info['table']['webViewsDir'] . '/' . 'popupForm.vue',
            $info['table']['controllerFile'],
            $info['table']['modelFile'],
            $info['table']['validateFile'],
        ];
        try {
            foreach ($files as &$file) {
                $file = Filesystem::fsFit(root_path() . $file);
                if (file_exists($file)) {
                    unlink($file);
                }
                Filesystem::delEmptyDir(dirname($file));
            }

            // 删除菜单
            Menu::delete(Helper::getMenuName($webLangDir), true);

            Helper::recordCrudStatus([
                'id'     => $id,
                'status' => 'delete',
            ]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success(__('Deleted successfully'));
    }

    /**
     * 获取文件路径数据
     * @throws Throwable
     */
    public function getFileData()
    {
        $table       = $this->request->get('table');
        $commonModel = $this->request->get('commonModel/b');

        if (!$table) {
            $this->error(__('Parameter error'));
        }

        try {
            $modelFile      = Helper::parseNameData($commonModel ? 'common' : 'admin', $table, 'model');
            $validateFile   = Helper::parseNameData('admin', $table, 'validate');
            $controllerFile = Helper::parseNameData('admin', $table, 'controller');
            $webViewsDir    = Helper::parseWebDirNameData($table, 'views');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        // 模型和控制器文件和文件列表
        $adminModelFiles      = Filesystem::getDirFiles(root_path() . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR);
        $commonModelFiles     = Filesystem::getDirFiles(root_path() . 'app' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR);
        $adminControllerFiles = get_controller_list();

        $modelFileList   = [];
        $controllerFiles = [];
        foreach ($adminModelFiles as $item) {
            $item                 = Filesystem::fsFit('app/admin/model/' . $item);
            $modelFileList[$item] = $item;
        }
        foreach ($commonModelFiles as $item) {
            $item                 = Filesystem::fsFit('app/common/model/' . $item);
            $modelFileList[$item] = $item;
        }

        $outExcludeController = [
            'Addon.php',
            'Ajax.php',
            'Dashboard.php',
            'Index.php',
            'Module.php',
            'Terminal.php',
            'routine/AdminInfo.php',
            'routine/Config.php',
        ];
        foreach ($adminControllerFiles as $item) {
            if (in_array($item, $outExcludeController)) {
                continue;
            }
            $item                   = Filesystem::fsFit('app/admin/controller/' . $item);
            $controllerFiles[$item] = $item;
        }

        $this->success('', [
            'modelFile'          => $modelFile['rootFileName'],
            'controllerFile'     => $controllerFile['rootFileName'],
            'validateFile'       => $validateFile['rootFileName'],
            'controllerFileList' => $controllerFiles,
            'modelFileList'      => $modelFileList,
            'webViewsDir'        => $webViewsDir['views'],
        ]);
    }

    /**
     * 检查是否已有CRUD记录
     * @throws Throwable
     */
    public function checkCrudLog()
    {
        $table   = $this->request->get('table');
        $crudLog = Db::name('crud_log')
            ->where('table_name', $table)
            ->order('create_time desc')
            ->find();
        $this->success('', [
            'id' => ($crudLog && $crudLog['status'] == 'success') ? $crudLog['id'] : 0,
        ]);
    }

    /**
     * 解析字段数据
     * @throws Throwable
     */
    public function parseFieldData()
    {
        AdminLog::setTitle(__('Parse field data'));
        $type  = $this->request->post('type');
        $table = $this->request->post('table');
        $table = TableManager::tableName($table);
        if ($type == 'db') {
            $sql       = 'SELECT * FROM `information_schema`.`tables` '
                . 'WHERE TABLE_SCHEMA = ? AND table_name = ?';
            $tableInfo = Db::query($sql, [config('database.connections.mysql.database'), $table]);
            if (!$tableInfo) {
                $this->error(__('Record not found'));
            }

            // 数据表是否有数据
            $adapter = TableManager::adapter(false);
            if ($adapter->hasTable($table)) {
                $empty = Db::table($table)->limit(1)->select()->isEmpty();
            } else {
                $empty = true;
            }

            $this->success('', [
                'columns' => Helper::parseTableColumns($table),
                'comment' => $tableInfo[0]['TABLE_COMMENT'] ?? '',
                'empty'   => $empty,
            ]);
        }
    }

    /**
     * 生成前检查
     * @throws Throwable
     */
    public function generateCheck()
    {
        $table          = $this->request->post('table');
        $controllerFile = $this->request->post('controllerFile', '');

        if (!$table) {
            $this->error(__('Parameter error'));
        }

        AdminLog::setTitle(__('Generate check'));

        try {
            if (!$controllerFile) {
                $controllerFile = Helper::parseNameData('admin', $table, 'controller')['rootFileName'];
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $tableList       = get_table_list();
        $tableExist      = array_key_exists(TableManager::tableName($table), $tableList);
        $controllerExist = file_exists(root_path() . $controllerFile);

        if ($controllerExist || $tableExist) {
            $this->error('', [
                'table'      => $tableExist,
                'controller' => $controllerExist,
            ], -1);
        }
        $this->success();
    }

    public function databaseList()
    {
        $tablePrefix     = config('database.connections.mysql.prefix');
        $outExcludeTable = [
            // 功能表
            'area',
            'token',
            'captcha',
            'admin_group_access',
            'config',
            'admin_log',
            // 不建议生成crud的表
            'user_money_log',
            'user_score_log',
        ];

        $outTables = [];
        $tables    = get_table_list();
        $pattern   = '/^' . $tablePrefix . '/i';
        foreach ($tables as $table => $tableComment) {
            if (!preg_match($pattern, $table)) continue;
            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $outExcludeTable)) {
                $outTables[$table] = $tableComment;
            }
        }
        $this->success('', [
            'dbs' => $outTables,
        ]);
    }

    /**
     * 关联表数据解析
     * @param $field
     * @throws Throwable
     */
    private function parseJoinData($field)
    {
        $dictEn   = [];
        $dictZhCn = [];

        if ($field['form']['relation-fields'] && $field['form']['remote-table']) {
            $columns        = Helper::parseTableColumns($field['form']['remote-table'], true);
            $relationFields = explode(',', $field['form']['relation-fields']);
            $tableName      = TableManager::tableName($field['form']['remote-table'], false);
            $rnPattern      = '/(.*)(_ids|_id)$/';
            if (preg_match($rnPattern, $field['name'])) {
                $relationName = parse_name(preg_replace($rnPattern, '$1', $field['name']), 1, false);
            } else {
                $relationName = parse_name($field['name'] . '_table', 1, false);
            }

            // 建立关联模型代码文件
            if (!$field['form']['remote-model'] || !file_exists(root_path() . $field['form']['remote-model'])) {
                $joinModelFile = Helper::parseNameData('admin', $tableName, 'model', $field['form']['remote-model']);
                if (!file_exists(root_path() . $joinModelFile['rootFileName'])) {
                    $joinModelData['append']             = [];
                    $joinModelData['methods']            = [];
                    $joinModelData['fieldType']          = [];
                    $joinModelData['createTime']         = '';
                    $joinModelData['updateTime']         = '';
                    $joinModelData['beforeInsertMixins'] = [];
                    $joinModelData['beforeInsert']       = '';
                    $joinModelData['afterInsert']        = '';
                    $joinModelData['name']               = $tableName;
                    $joinModelData['className']          = $joinModelFile['lastName'];
                    $joinModelData['namespace']          = $joinModelFile['namespace'];
                    $joinTablePk                         = 'id';
                    $joinFieldsMap                       = [];
                    foreach ($columns as $column) {
                        $joinFieldsMap[$column['name']] = $column['designType'];
                        $this->parseModelMethods($column, $joinModelData);
                        if ($column['primaryKey']) $joinTablePk = $column['name'];
                    }
                    $weighKey = array_search('weigh', $joinFieldsMap);
                    if ($weighKey !== false) {
                        $joinModelData['afterInsert'] = Helper::assembleStub('mixins/model/afterInsert', [
                            'field' => $joinFieldsMap[$weighKey]
                        ]);
                    }
                    Helper::writeModelFile($joinTablePk, $joinFieldsMap, $joinModelData, $joinModelFile);
                }
                $field['form']['remote-model'] = $joinModelFile['rootFileName'];
            }

            if ($field['designType'] == 'remoteSelect') {
                // 关联预载入方法
                $this->controllerData['attr']['withJoinTable'][$relationName] = $relationName;

                // 模型方法代码
                $relationData                                         = [
                    'relationMethod'     => $relationName,
                    'relationMode'       => 'belongsTo',
                    'relationPrimaryKey' => $field['form']['remote-pk'] ?? 'id',
                    'relationForeignKey' => $field['name'],
                    'relationClassName'  => str_replace(['.php', '/'], ['', '\\'], '\\' . $field['form']['remote-model']) . "::class",
                ];
                $this->modelData['relationMethodList'][$relationName] = Helper::assembleStub('mixins/model/belongsTo', $relationData);

                // 查询时显示的字段
                if ($relationFields) {
                    $this->controllerData['relationVisibleFieldList'][$relationData['relationMethod']] = $relationFields;
                }
            } elseif ($field['designType'] == 'remoteSelects') {
                $this->modelData['append'][]  = $relationName;
                $this->modelData['methods'][] = Helper::assembleStub('mixins/model/getters/remoteSelectLabels', [
                    'field'          => parse_name($relationName, 1),
                    'className'      => str_replace(['.php', '/'], ['', '\\'], '\\' . $field['form']['remote-model']),
                    'primaryKey'     => $field['form']['remote-pk'] ?? 'id',
                    'foreignKey'     => $field['name'],
                    'labelFieldName' => $field['form']['remote-field'] ?? 'name',
                ]);
            }

            foreach ($relationFields as $relationField) {
                if (!array_key_exists($relationField, $columns)) continue;
                $relationFieldPrefix     = $relationName . '.';
                $relationFieldLangPrefix = strtolower($relationName) . '__';
                Helper::getDictData($dictEn, $columns[$relationField], 'en', $relationFieldLangPrefix);
                Helper::getDictData($dictZhCn, $columns[$relationField], 'zh-cn', $relationFieldLangPrefix);

                // 不允许双击编辑的字段
                if ($columns[$relationField]['designType'] == 'switch') {
                    $this->indexVueData['dblClickNotEditColumn'][] = $field['name'];
                }

                // 列字典数据
                $columnDict = $this->getColumnDict($columns[$relationField], $relationFieldLangPrefix);

                // 表格列
                $columns[$relationField]['designType']      = $field['designType'];
                $columns[$relationField]['table']['render'] = 'tags';
                if ($field['designType'] == 'remoteSelects') {
                    $columns[$relationField]['table']['operator'] = 'false';
                    $this->indexVueData['tableColumn'][]          = $this->getTableColumn($columns[$relationField], $columnDict, $relationFieldPrefix, $relationFieldLangPrefix);

                    // 额外生成一个公共搜索，渲染为远程下拉的列
                    unset($columns[$relationField]['table']['render']);
                    $columns[$relationField]['table']['label']           = "t('" . $this->webTranslate . $relationFieldLangPrefix . $columns[$relationField]['name'] . "')";
                    $columns[$relationField]['name']                     = $field['name'];
                    $columns[$relationField]['table']['show']            = 'false';
                    $columns[$relationField]['table']['operator']        = 'FIND_IN_SET';
                    $columns[$relationField]['table']['comSearchRender'] = 'remoteSelect';
                    $columns[$relationField]['table']['remote']          = [
                        'pk'        => TableManager::tableName($field['form']['remote-table']) . '.' . ($field['form']['remote-pk'] ?? 'id'),
                        'field'     => $field['form']['remote-field'] ?? 'name',
                        'remoteUrl' => $this->getRemoteSelectUrl($field),
                        'multiple'  => 'true',
                    ];
                    $this->indexVueData['tableColumn'][]                 = $this->getTableColumn($columns[$relationField], $columnDict, '', $relationFieldLangPrefix);
                } else {
                    $columns[$relationField]['table']['operator'] = 'LIKE';
                    $this->indexVueData['tableColumn'][]          = $this->getTableColumn($columns[$relationField], $columnDict, $relationFieldPrefix, $relationFieldLangPrefix);
                }
            }
        }
        $this->langTsData['en']    = array_merge($this->langTsData['en'], $dictEn);
        $this->langTsData['zh-cn'] = array_merge($this->langTsData['zh-cn'], $dictZhCn);
    }

    /**
     * 解析模型方法（设置器、获取器等）
     */
    private function parseModelMethods($field, &$modelData)
    {
        // fieldType
        if ($field['designType'] == 'array') {
            $modelData['fieldType'][$field['name']] = 'json';
        } elseif (!in_array($field['name'], ['create_time', 'update_time', 'updatetime', 'createtime']) && $field['designType'] == 'datetime' && (in_array($field['type'], ['int', 'bigint']))) {
            $modelData['fieldType'][$field['name']] = 'timestamp:Y-m-d H:i:s';
        }

        // beforeInsertMixins
        if ($field['designType'] == 'spk') {
            $modelData['beforeInsertMixins']['snowflake'] = Helper::assembleStub('mixins/model/mixins/beforeInsertWithSnowflake', []);
        }

        // methods
        $fieldName = parse_name($field['name'], 1);
        if (in_array($field['designType'], $this->dtStringToArray)) {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/stringToArray', [
                'field' => $fieldName
            ]);
            $modelData['methods'][] = Helper::assembleStub('mixins/model/setters/arrayToString', [
                'field' => $fieldName
            ]);
        } elseif ($field['designType'] == 'array') {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/jsonDecode', [
                'field' => $fieldName
            ]);
        } elseif ($field['designType'] == 'time') {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/setters/time', [
                'field' => $fieldName
            ]);
        } elseif ($field['designType'] == 'editor') {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/htmlDecode', [
                'field' => $fieldName
            ]);
        } elseif ($field['designType'] == 'spk') {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/string', [
                'field' => $fieldName
            ]);
        } elseif ($field['originalDesignType'] == 'float') {
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/float', [
                'field' => $fieldName
            ]);
        }

        if ($field['designType'] == 'city') {
            $modelData['append'][]  = $field['name'] . '_text';
            $modelData['methods'][] = Helper::assembleStub('mixins/model/getters/cityNames', [
                'field'             => $fieldName . 'Text',
                'originalFieldName' => $field['name'],
            ]);
        }
    }

    /**
     * 控制器/模型等文件的一些杂项属性解析
     */
    private function parseSundryData($field, $table)
    {
        if ($field['designType'] == 'editor') {
            $this->formVueData['bigDialog']     = 'true'; // form 使用较宽的 Dialog
            $this->controllerData['filterRule'] = "\n" . Helper::tab(2) . '$this->request->filter(\'clean_xss\');';// 修改变量过滤规则
        }

        // 默认排序字段
        if ($table['defaultSortField'] && $table['defaultSortType']) {
            $defaultSortField = "{$table['defaultSortField']},{$table['defaultSortType']}";
            if ($defaultSortField == 'id,desc') {
                $this->controllerData['attr']['defaultSortField'] = '';
            } else {
                $this->controllerData['attr']['defaultSortField'] = $defaultSortField;
                $this->indexVueData['defaultOrder']               = Helper::buildDefaultOrder($table['defaultSortField'], $table['defaultSortType']);
            }
        }
    }

    private function getFormField($field, $columnDict): array
    {
        // 表单项属性
        $formField = [
            ':label'  => 't(\'' . $this->webTranslate . $field['name'] . '\')',
            'type'    => $field['designType'],
            'v-model' => 'baTable.form.items!.' . $field['name'],
            'prop'    => $field['name'],
        ];

        // 不同输入框的属性处理
        if ($columnDict || in_array($field['designType'], ['radio', 'checkbox', 'select', 'selects'])) {
            $formField[':data'] = [
                'content' => $columnDict,
            ];
        } elseif ($field['designType'] == 'textarea') {
            $formField[':input-attr']['rows'] = (int)($field['form']['rows'] ?? 3);
            $formField['@keyup.enter.stop']   = '';
            $formField['@keyup.ctrl.enter']   = 'baTable.onSubmit(formRef)';
        } elseif ($field['designType'] == 'remoteSelect' || $field['designType'] == 'remoteSelects') {
            $formField[':input-attr']['pk']         = TableManager::tableName($field['form']['remote-table']) . '.' . ($field['form']['remote-pk'] ?? 'id');
            $formField[':input-attr']['field']      = $field['form']['remote-field'] ?? 'name';
            $formField[':input-attr']['remote-url'] = $this->getRemoteSelectUrl($field);
        } elseif ($field['designType'] == 'number') {
            $formField[':input-attr']['step'] = (int)($field['form']['step'] ?? 1);
            $formField['v-model.number']      = $formField['v-model'];
            unset($formField['v-model']);
        } elseif ($field['designType'] == 'icon') {
            $formField[':input-attr']['placement'] = 'top';
        } elseif ($field['designType'] == 'editor') {
            $formField['@keyup.enter.stop'] = '';
            $formField['@keyup.ctrl.enter'] = 'baTable.onSubmit(formRef)';
        }

        // placeholder
        if (!in_array($field['designType'], ['image', 'images', 'file', 'files', 'switch'])) {
            if (in_array($field['designType'], ['radio', 'checkbox', 'datetime', 'year', 'date', 'time', 'select', 'selects', 'remoteSelect', 'remoteSelects', 'city', 'icon'])) {
                $formField[':placeholder'] = "t('Please select field', { field: t('" . $this->webTranslate . $field['name'] . "') })";
            } else {
                $formField[':placeholder'] = "t('Please input field', { field: t('" . $this->webTranslate . $field['name'] . "') })";
            }
        }

        // 默认值
        if ($field['default'] && $field['default'] != 'empty string') {
            $this->indexVueData['defaultItems'][$field['name']] = $field['default'];
        }
        if ($field['default'] == 'null') {
            $this->indexVueData['defaultItems'][$field['name']] = null;
        } elseif ($field['default'] == '0' && in_array($field['designType'], ['radio', 'checkbox', 'select', 'selects'])) {
            // 防止为`0`时无法设置上默认值
            $this->indexVueData['defaultItems'][$field['name']] = '0';
        }
        if ($field['designType'] == 'array') {
            $this->indexVueData['defaultItems'][$field['name']] = "[]";
        } elseif (in_array($field['designType'], $this->dtStringToArray) && $field['default'] !== null && stripos($field['default'], ',') !== false) {
            $this->indexVueData['defaultItems'][$field['name']] = Helper::buildSimpleArray(explode(',', $field['default']));
        } elseif (in_array($field['designType'], ['weigh', 'number', 'float'])) {
            $this->indexVueData['defaultItems'][$field['name']] = (float)$field['default'];
        }
        return $formField;
    }

    private function getRemoteSelectUrl($field): string
    {
        if ($field['form']['remote-url']) return $field['form']['remote-url'];
        $url = '';
        if ($field['form']['remote-controller']) {
            $pathArr      = [];
            $controller   = explode(DIRECTORY_SEPARATOR, $field['form']['remote-controller']);
            $controller   = str_replace('.php', '', $controller);
            $redundantDir = [
                'app'        => 0,
                'admin'      => 1,
                'controller' => 2,
            ];
            foreach ($controller as $key => $item) {
                if (!array_key_exists($item, $redundantDir) || $key !== $redundantDir[$item]) {
                    $pathArr[] = $item;
                }
            }
            $url = count($pathArr) > 1 ? implode('.', $pathArr) : $pathArr[0];
            $url = '/admin/' . $url . '/index';
        }
        return $url;
    }

    private function getTableColumn($field, $columnDict, $fieldNamePrefix = '', $translationPrefix = ''): array
    {
        $column = [
            'label' => "t('" . $this->webTranslate . $translationPrefix . $field['name'] . "')",
            'prop'  => $fieldNamePrefix . $field['name'] . ($field['designType'] == 'city' ? '_text' : ''),
            'align' => 'center',
        ];

        // 模糊搜索增加一个placeholder
        if (isset($field['table']['operator']) && $field['table']['operator'] == 'LIKE') {
            $column['operatorPlaceholder'] = "t('Fuzzy query')";
        }

        // 合并前端预设的字段表格属性
        if (isset($field['table']) && $field['table']) {
            $column = array_merge($column, $field['table']);
        }

        // 需要值替换的渲染类型
        $columnReplaceValue = ['tag', 'tags', 'switch'];
        if (!in_array($field['designType'], ['remoteSelect', 'remoteSelects']) && ($columnDict || (isset($field['table']['render']) && in_array($field['table']['render'], $columnReplaceValue)))) {
            $column['replaceValue'] = $columnDict;
        }

        if (isset($column['render']) && $column['render'] == 'none') {
            unset($column['render']);
        }
        return $column;
    }

    private function getColumnDict($column, $translationPrefix = ''): array
    {
        $dict = [];
        // 确保字典中无翻译也可以识别到该值
        if (in_array($column['type'], ['enum', 'set'])) {
            $dataType   = str_replace(' ', '', $column['dataType']);
            $columnData = substr($dataType, stripos($dataType, '(') + 1, -1);
            $columnData = explode(',', str_replace(["'", '"'], '', $columnData));
            foreach ($columnData as $columnDatum) {
                $dict[$columnDatum] = $column['name'] . ' ' . $columnDatum;
            }
        }
        $dictData = [];
        Helper::getDictData($dictData, $column, 'zh-cn', $translationPrefix);
        if ($dictData) {
            unset($dictData[$translationPrefix . $column['name']]);
            foreach ($dictData as $key => $item) {
                $keyName        = str_replace($translationPrefix . $column['name'] . ' ', '', $key);
                $dict[$keyName] = "t('" . $this->webTranslate . $key . "')";
            }
        }
        return $dict;
    }
}