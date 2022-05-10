<?php

namespace ba;

/**
 * 版本类
 */
class Version
{
    /**
     * 提取版本号中的数字
     * @param $str
     * @return array|string|string[]|null
     */
    public static function reg(string $str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }

    /**
     * 版本号位数不足时补位0
     * @param $str
     * @param $length
     * @return string
     */
    public static function cover(string $str, int $length): string
    {
        return str_pad($str, $length, '0');
    }

    /**
     * 比较两个版本号
     * @param $v1 string 要求的版本号
     * @param $v2 string | bool 被比较版本号
     * @return bool 是否达到要求的版本号
     */
    public static function compare(string $v1, $v2): bool
    {
        if (!$v2) {
            return false;
        }

        // 删除开头的 V
        if (strtolower($v2[0]) == 'v') {
            $v2 = substr($v2, 1);
        }

        if ($v1 == "*" || $v1 == $v2) {
            return true;
        }

        $length = strlen(self::reg($v1)) > strlen(self::reg($v2)) ? strlen(self::reg($v1)) : strlen(self::reg($v2));
        $v1     = self::cover(self::reg($v1), $length);
        $v2     = self::cover(self::reg($v2), $length);
        if ($v1 == $v2) {
            return true;
        } else {
            return $v2 > $v1;
        }
    }

    public static function checkDigitalVersion($version)
    {
        if (!$version) {
            return false;
        }
        if (strtolower($version[0]) == 'v') {
            $version = substr($version, 1);
        }

        $rule1 = '/\.{2,10}/'; // 是否有两个的`.`
        $rule2 = '/^\d+(\.\d+){0,10}$/';
        if (!preg_match($rule1, (string)$version)) {
            return !!preg_match($rule2, (string)$version);
        }
        return false;
    }

    public static function getNpmVersion()
    {
        $execOut = CommandExec::instance(false)->getOutputFromPopen('npm-v');
        if ($execOut && isset($execOut[0]) && self::checkDigitalVersion($execOut[0])) {
            return $execOut[0];
        } else {
            return false;
        }
    }

    public static function getCnpmVersion()
    {
        $execOut = CommandExec::instance(false)->getOutputFromPopen('cnpm-v');
        if ($execOut && isset($execOut[0])) {
            $preg = '/cnpm@(.+?) \(/is';
            preg_match($preg, $execOut[0], $result);
            return $result[1] ?? false;
        } else {
            return false;
        }
    }

    public static function getNodeJsVersion()
    {
        $execOut = CommandExec::instance(false)->getOutputFromPopen('node-v');
        if ($execOut && isset($execOut[0]) && self::checkDigitalVersion($execOut[0])) {
            return $execOut[0];
        } else {
            return false;
        }
    }
}