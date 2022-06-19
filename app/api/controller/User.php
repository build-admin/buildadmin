<?php

namespace app\api\controller;

use app\common\controller\Frontend;

class User extends Frontend
{
    protected $noNeedLogin = ['login', 'register'];

    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {

    }

    public function login()
    {

    }

    public function register()
    {

    }
}