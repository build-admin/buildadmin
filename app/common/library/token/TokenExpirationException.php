<?php

namespace app\common\library\token;

use think\Exception;

/**
 * Token过期异常
 */
class TokenExpirationException extends Exception
{
    public function __construct(protected $message = '', protected $code = 409, protected $data = [])
    {
        parent::__construct($message, $code);
    }
}