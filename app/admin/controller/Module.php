<?php

namespace app\admin\controller;

use ba\module\Server;
use think\Exception;
use ba\module\Manage;
use ba\module\moduleException;
use app\common\controller\Backend;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Module extends Backend
{
    protected $noNeedPermission = ['*'];

    public function index()
    {
        // 已安装的模块
        $installedModule = [];
        $excludedDirs    = ['ebak'];
        $moduleDir       = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        if (is_dir($moduleDir)) {
            foreach (
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($moduleDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CATCH_GET_CHILD
                ) as $item
            ) {
                $dirName = $iterator->getSubPathName();
                if (!in_array($dirName, $excludedDirs)) {
                    $installedModule[] = Server::getIni($moduleDir . $dirName . DIRECTORY_SEPARATOR);
                }
            }
        }

        $this->success('', [
            'installedModule' => $installedModule
        ]);
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
        $res = [];
        try {
            $res = Manage::instance($uid)->install($token, $orderId);
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'data' => $res,
        ]);
    }

    public function dependentInstallComplete()
    {
        $uid     = $this->request->get("uid/s", '');
        $type    = $this->request->get("type/s", '');
        $typeArr = ['npm', 'composer', 'all'];
        if (!$uid || !in_array($type, $typeArr)) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->dependentInstallComplete($type);
        } catch (moduleException|Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function changeState()
    {
        $uid   = $this->request->get("uid/s", '');
        $state = $this->request->get("state/b", false);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->changeState($state);
        } catch (moduleException|Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }
}