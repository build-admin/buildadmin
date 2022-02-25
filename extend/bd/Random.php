<?php

namespace bd;

class Random
{
    /**
     * 获取全球唯一标识
     * @return string
     */
    public static function uuid()
    {
        return sprintf(
                '%04x%04x-%04x-%04x-%04x-',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000
            ) . substr(md5(uniqid(mt_rand(), true)), 0, 12);
    }

}