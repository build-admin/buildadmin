<?php

namespace app\admin\controller;

use ba\Terminal as T;

/**
 * WEB终端
 * Content-Type: text/event-stream
 */
class Terminal
{
    public function index()
    {
        T::instance()->exec();
    }
}