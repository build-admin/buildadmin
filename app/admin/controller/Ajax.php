<?php

namespace app\admin\controller;

use Throwable;
use ba\Terminal;
use think\Response;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Event;
use app\admin\model\AdminLog;
use app\common\library\Upload;
use app\common\controller\Backend;

class Ajax extends Backend
{
    protected array $noNeedPermission = ['*'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload()
    {
        AdminLog::setTitle(__('upload'));
        $file = $this->request->file('file');
        try {
            $upload     = new Upload($file);
            $attachment = $upload->upload(null, $this->auth->id);
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
    public function area()
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
     * 获取表主键字段
     * @param ?string $table
     * @throws Throwable
     */
    public function getTablePk(?string $table = null)
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }
        $tablePk = Db::query("SHOW TABLE STATUS LIKE '$table'", [], true);
        if (!$tablePk) {
            $table   = config('database.connections.mysql.prefix') . $table;
            $tablePk = Db::query("SHOW TABLE STATUS LIKE '$table'", [], true);
            if (!$tablePk) {
                $this->error(__('Data table does not exist'));
            }
        }
        $tablePk = Db::table($table)->getPk();
        $this->success('', ['pk' => $tablePk]);
    }

    public function getTableFieldList()
    {
        $table = $this->request->param('table');
        $clean = $this->request->param('clean', true);
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $tablePk = Db::name($table)->getPk();
        $this->success('', [
            'pk'        => $tablePk,
            'fieldList' => get_table_fields($table, $clean),
        ]);
    }

    public function changeTerminalConfig()
    {
        AdminLog::setTitle(__('changeTerminalConfig'));
        if (Terminal::changeTerminalConfig()) {
            $this->success();
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearCache()
    {
        $type = $this->request->post('type');
        if ($type == 'tp' || $type == 'all') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }
}