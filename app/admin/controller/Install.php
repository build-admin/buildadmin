<?php

namespace app\admin\controller;

use ba\CommandExec;

/**
 * 安装控制器
 * Content-Type: text/event-stream
 */
class Install
{
    public function terminal()
    {
        CommandExec::instance()->terminal();
    }
}