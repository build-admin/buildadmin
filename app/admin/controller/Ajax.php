<?php

namespace app\admin\controller;

use Throwable;
use ba\Terminal;
use think\Response;
use ba\TableManager;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Event;
use app\admin\model\AdminLog;
use app\common\library\Upload;
use app\common\controller\Backend;

class Ajax extends Backend
{
    protected array $noNeedPermission = ['*'];

    /**
     * 无需登录的方法
     * terminal 内部自带验权
     */
    protected array $noNeedLogin = ['terminal'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload(): void
    {
        AdminLog::instance()->setTitle(__('upload'));
        $file   = $this->request->file('file');
        $driver = $this->request->param('driver', 'local');
        $topic  = $this->request->param('topic', 'default');
        try {
            $upload     = new Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, $this->auth->id);
            unset($attachment['create_time'], $attachment['quote']);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment ?? []
        ]);
    }

    /**
     * 获取省市区数据
     * @throws Throwable
     */
    public function area(): void
    {
        $this->success('', get_area());
    }

    public function buildSuffixSvg(): Response
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    /**
     * 获取已脱敏的数据库连接配置列表
     * @throws Throwable
     */
    public function getDatabaseConnectionList(): void
    {
        $quickSearch     = $this->request->get("quickSearch/s", '');
        $connections     = config('database.connections');
        $desensitization = [];
        foreach ($connections as $key => $connection) {
            $connection        = TableManager::getConnectionConfig($key);
            $desensitization[] = [
                'type'     => $connection['type'],
                'database' => substr_replace($connection['database'], '****', 1, strlen($connection['database']) > 4 ? 2 : 1),
                'key'      => $key,
            ];
        }

        if ($quickSearch) {
            $desensitization = array_filter($desensitization, function ($item) use ($quickSearch) {
                return preg_match("/$quickSearch/i", $item['key']);
            });
            $desensitization = array_values($desensitization);
        }

        $this->success('', [
            'list' => $desensitization,
        ]);
    }

    /**
     * 获取表主键字段
     * @param ?string $table
     * @param ?string $connection
     * @throws Throwable
     */
    public function getTablePk(?string $table = null, ?string $connection = null): void
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $table = TableManager::tableName($table, true, $connection);
        if (!TableManager::phinxAdapter(false, $connection)->hasTable($table)) {
            $this->error(__('Data table does not exist'));
        }

        $tablePk = Db::connect(TableManager::getConnection($connection))
            ->table($table)
            ->getPk();
        $this->success('', ['pk' => $tablePk]);
    }

    /**
     * 获取数据表列表
     * @throws Throwable
     */
    public function getTableList(): void
    {
        $quickSearch  = $this->request->get("quickSearch/s", '');
        $connection   = $this->request->request('connection');// 数据库连接配置标识
        $samePrefix   = $this->request->request('samePrefix/b', true);// 是否仅返回项目数据表（前缀同项目一致的）
        $excludeTable = $this->request->request('excludeTable/a', []);// 要排除的数据表数组（表名无需带前缀）

        $outTables = [];
        $dbConfig  = TableManager::getConnectionConfig($connection);
        $tables    = TableManager::getTableList($connection);

        if ($quickSearch) {
            $tables = array_filter($tables, function ($comment) use ($quickSearch) {
                return preg_match("/$quickSearch/i", $comment);
            });
        }

        $pattern = '/^' . $dbConfig['prefix'] . '/i';
        foreach ($tables as $table => $comment) {
            if ($samePrefix && !preg_match($pattern, $table)) continue;

            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $excludeTable)) {
                $outTables[] = [
                    'table'      => $table,
                    'comment'    => $comment,
                    'connection' => $connection,
                    'prefix'     => $dbConfig['prefix'],
                ];
            }
        }

        $this->success('', [
            'list' => $outTables,
        ]);
    }

    /**
     * 获取数据表字段列表
     * @throws Throwable
     */
    public function getTableFieldList(): void
    {
        $table      = $this->request->param('table');
        $clean      = $this->request->param('clean', true);
        $connection = $this->request->request('connection');
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $connection = TableManager::getConnection($connection);
        $tablePk    = Db::connect($connection)->name($table)->getPk();
        $this->success('', [
            'pk'        => $tablePk,
            'fieldList' => TableManager::getTableColumns($table, $clean, $connection),
        ]);
    }

    public function changeTerminalConfig(): void
    {
        AdminLog::instance()->setTitle(__('Change terminal config'));
        if (Terminal::changeTerminalConfig()) {
            $this->success();
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearCache(): void
    {
        AdminLog::instance()->setTitle(__('Clear cache'));
        $type = $this->request->post('type');
        if ($type == 'tp' || $type == 'all') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }

    /**
     * 终端
     * @throws Throwable
     */
    public function terminal(): void
    {
        (new Terminal())->exec();
    }
}