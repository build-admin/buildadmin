<?php

namespace app\admin\controller;

use think\Exception;
use ba\module\Manage;
use ba\module\moduleException;
use app\common\controller\Backend;

class Module extends Backend
{
    protected $noNeedPermission = ['*'];

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