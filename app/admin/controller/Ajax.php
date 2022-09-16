<?php

namespace app\admin\controller;

use ba\Terminal;
use think\Exception;
use think\facade\Db;
use think\facade\Cache;
use app\admin\model\AdminLog;
use app\common\library\Upload;
use think\exception\FileException;
use app\common\controller\Backend;

class Ajax extends Backend
{
    protected $noNeedPermission = ['*'];

    public function initialize()
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
            unset($attachment['createtime'], $attachment['quote']);
        } catch (Exception|FileException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment ?? []
        ]);
    }

    public function area()
    {
        $this->success('', get_area());
    }

    public function buildSuffixSvg()
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    public function getTablePk($table = null)
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }
        $tablePk = Db::query("SHOW TABLE STATUS LIKE '{$table}'", [], true);
        if (!$tablePk) {
            $table   = config('database.connections.mysql.prefix') . $table;
            $tablePk = Db::query("SHOW TABLE STATUS LIKE '{$table}'", [], true);
            if (!$tablePk) {
                $this->error(__('Data table does not exist'));
            }
        }
        $tablePk = Db::table($table)->getPk();
        $this->success('', ['pk' => $tablePk]);
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
        $this->success(__('Cache cleaned~'));
    }
}