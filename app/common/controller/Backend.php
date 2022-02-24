<?php

namespace app\common\controller;

use app\common\controller\Api;
use app\admin\library\Auth;

class Backend extends Api
{
    protected $noNeedLogin      = [];
    protected $noNeedPermission = [];

    protected $auth = null;

    protected $model = null;
}