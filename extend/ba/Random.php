<?php

namespace ba;

class Random
{
    /**
     * 获取全球唯一标识
     * @return string
     */
    public static function uuid(): string
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

    /**
     * 随机字符生成
     * @param string $type 类型 alpha/alnum/numeric/noZero/unique/md5/encrypt/sha1
     * @param int    $len  长度
     * @return string
     */
    public static function build(string $type = 'alnum', int $len = 8): string
    {
        switch ($type) {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'noZero':
                $pool = match ($type) {
                    'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'alnum' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'numeric' => '0123456789',
                    'noZero' => '123456789',
                    default => '',
                };
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
        return '';
    }

}