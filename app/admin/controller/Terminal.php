<?php

namespace app\admin\controller;

use Throwable;
use ba\Terminal as T;

/**
 * WEB终端
 * Content-Type: text/event-stream
 */
class Terminal
{
    /**
     * 终端
     * @throws Throwable
     */
    public function index(): void
    {
        T::instance()->exec();
    }
}