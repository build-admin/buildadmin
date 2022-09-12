<?php

namespace ba\module;

use think\Exception;

class moduleException extends Exception
{
    public function __construct($message, $code = 0, $data = '')
    {
        $this->message = $message;
        $this->code    = $code;
        $this->data    = $data;

        parent::__construct($message, $code);
    }
}