<?php

namespace app\common\service;

use ba\module\Server;
use think\Service;
use think\facade\Event;

class moduleService extends Service
{
    public function register()
    {
        $this->moduleAppInit();
    }

    public function moduleAppInit()
    {
        $installed = Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR);
        foreach ($installed as $item) {
            $moduleClass = Server::getClass($item['uid']);
            if (class_exists($moduleClass)) {
                if (method_exists($moduleClass, 'AppInit')) {
                    Event::listen('AppInit', function () use ($moduleClass) {
                        $handle = new $moduleClass();
                        $handle->AppInit();
                    });
                }
            }
        }
    }
}