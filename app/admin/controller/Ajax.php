<?php

namespace app\admin\controller;

use ba\CommandExec;
use think\Exception;
use think\exception\FileException;
use app\common\library\Upload;
use app\common\controller\Backend;
use think\facade\Cache;
use think\facade\Db;
use app\admin\model\AdminLog;
use ba\module\Manage;
use ba\module\moduleException;

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
            'file' => $attachment
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
        if (CommandExec::instance(false)->changeTerminalConfig()) {
            $this->success('');
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearCache()
    {
        $type = $this->request->post('type');
        if ($type == 'tp') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        $this->success(__('Cache cleaned~'));
    }

    public function installState()
    {
        $uid = $this->request->get("uid/s", '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $this->success('', [
            'state' => Manage::instance($uid)->installState()
        ]);
    }

    public function installModule()
    {
        $uid     = $this->request->get("uid/s", '');
        $token   = $this->request->get("token/s", '');
        $orderId = $this->request->get("order_id/d", 0);
        if (!$token || !$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->install($token, $orderId);
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function dependentInstallComplete()
    {
        $uid     = $this->request->get("uid/s", '');
        $type    = $this->request->get("type/s", '');
        $typeArr = ['npm', 'composer', 'all'];
        if (!$uid || !in_array($typeArr, $type)) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->dependentInstallComplete($type);
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }
}