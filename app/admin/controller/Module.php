<?php

namespace app\admin\controller;

use Throwable;
use ba\module\Server;
use ba\module\Manage;
use ba\module\moduleException;
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
            'installed' => Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR),
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
        $uid     = $this->request->get("uid/s", '');
        $token   = $this->request->get("token/s", '');
        $orderId = $this->request->get("order_id/d", 0);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $res = [];
        try {
            $res = Manage::instance($uid)->install($token, $orderId);
        } catch (moduleException $e) {
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
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function changeState()
    {
        $uid   = $this->request->post("uid/s", '');
        $state = $this->request->post("state/b", false);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = Manage::instance($uid)->changeState($state);
        } catch (moduleException $e) {
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
        $uid = $this->request->get("uid/s", '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->uninstall();
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function update()
    {
        $uid     = $this->request->get("uid/s", '');
        $token   = $this->request->get("token/s", '');
        $orderId = $this->request->get("order_id/d", 0);
        if (!$token || !$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->update($token, $orderId);
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function upload()
    {
        $file = $this->request->get("file/s", '');
        if (!$file) {
            $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = Manage::instance()->upload($file);
        } catch (moduleException $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'info' => $info
        ]);
    }
}