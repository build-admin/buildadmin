<?php

namespace app\common\library\token\driver;

use app\common\library\token\Driver;

class Redis
{
    /**
     * 默认配置
     * @var array
     */
    protected $options = [];

    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        echo '待完善' . PHP_EOL;
        echo '---Redis配置信息---' . PHP_EOL;
        print_r($this->options);
    }

}