<?php

namespace ba;

use think\Exception as E;

/**
 * BuildAdmin通用异常类
 * catch 到异常后可以直接 $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
 */
class Exception extends E
{
    public function __construct(protected $message, protected $code = 0, protected $data = [])
    {
        parent::__construct($message, $code);
    }
}