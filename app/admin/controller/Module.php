<?php

namespace app\admin\controller;

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
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($moduleDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CATCH_GET_CHILD
            ) as $item
        ) {
            $dirName = $iterator->getSubPathName();
            if (!in_array($dirName, $excludedDirs)) {
                $installedModule[] = Manage::instance($dirName)->getInfo();
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
        if (!$uid || !in_array($type, $typeArr)) {
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