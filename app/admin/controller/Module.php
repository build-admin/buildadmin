<?php

namespace app\admin\controller;

use Throwable;
use ba\Exception;
use think\facade\Config;
use app\admin\model\AdminLog;
use app\admin\library\module\Server;
use app\admin\library\module\Manage;
use app\common\controller\Backend;

class Module extends Backend
{
    protected array $noNeedPermission = ['state', 'dependentInstallComplete'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $this->success('', [
            'sysVersion' => Config::get('buildadmin.version'),
            'installed'  => Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR),
        ]);
    }

    public function state()
    {
        $uid = $this->request->get("uid/s", '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $this->success('', [
            'state' => Manage::instance($uid)->getInstallState()
        ]);
    }

    public function install()
    {
        AdminLog::setTitle(__('Install module'));
        $uid     = $this->request->get("uid/s", '');
        $token   = $this->request->get("token/s", '');
        $orderId = $this->request->get("orderId/d", 0);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $res = [];
        try {
            $res = Manage::instance($uid)->install($token, $orderId);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'data' => $res,
        ]);
    }

    public function dependentInstallComplete()
    {
        $uid = $this->request->get("uid/s", '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->dependentInstallComplete('all');
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function changeState()
    {
        AdminLog::setTitle(__('Change module state'));
        $uid   = $this->request->post("uid/s", '');
        $state = $this->request->post("state/b", false);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = Manage::instance($uid)->changeState($state);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'info' => $info,
        ]);
    }

    public function uninstall()
    {
        AdminLog::setTitle(__('Unload module'));
        $uid = $this->request->get("uid/s", '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->uninstall();
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function update()
    {
        AdminLog::setTitle(__('Update module'));
        $uid     = $this->request->get("uid/s", '');
        $token   = $this->request->get("token/s", '');
        $orderId = $this->request->get("orderId/d", 0);
        if (!$token || !$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->update($token, $orderId);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function upload()
    {
        AdminLog::setTitle(__('Upload install module'));
        $file = $this->request->get("file/s", '');
        if (!$file) {
            $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = Manage::instance()->upload($file);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'info' => $info
        ]);
    }
}