<?php

namespace ba;

use think\Exception;

/**
 * 版本类
 */
class Version
{

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
        if (strtolower($v1[0]) == 'v') {
            $v1 = substr($v1, 1);
        }
        if (strtolower($v2[0]) == 'v') {
            $v2 = substr($v2, 1);
        }

        if ($v1 == "*" || $v1 == $v2) {
            return true;
        }

        // 丢弃'-'后面的内容
        if (strpos($v1, '-') !== false) $v1 = explode('-', $v1)[0];
        if (strpos($v2, '-') !== false) $v2 = explode('-', $v2)[0];

        $v1 = explode('.', $v1);
        $v2 = explode('.', $v2);
        if (!is_array($v1) || !is_array($v2)) {
            throw new Exception('Version number format error');
        }

        // 将号码逐个进行比较
        for ($i = 0; $i < count($v1); $i++) {
            if (!isset($v2[$i])) {
                break;
            }
            if ($v1[$i] == $v2[$i]) {
                continue;
            }
            if ($v1[$i] > $v2[$i]) {
                return false;
            }
            if ($v1[$i] < $v2[$i]) {
                return true;
            }
        }
        if (count($v1) != count($v2)) {
            return !(count($v1) > count($v2));
        }
        throw new Exception('Version number comparison failed');
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

    public static function getCnpmVersion()
    {
        $execOut = CommandExec::instance(false)->getOutputFromPopen('version-view.cnpm');
        if ($execOut) {
            $execOut = implode('', $execOut);
            $preg    = '/cnpm@(.+?) \(/is';
            preg_match($preg, $execOut, $result);
            return $result[1] ?? false;
        } else {
            return false;
        }
    }

    /**
     * 获取依赖版本号
     * @param string $name 支持：npm、cnpm、yarn、pnpm、node
     */
    public static function getVersion(string $name)
    {
        if ($name == 'cnpm') {
            return self::getCnpmVersion();
        } elseif (in_array($name, ['npm', 'yarn', 'pnpm', 'node'])) {
            $execOut = CommandExec::instance(false)->getOutputFromPopen('version-view.' . $name);
            if ($execOut) {
                // 检测两行，第一行可能会是个警告消息
                for ($i = 0; $i < 2; $i++) {
                    if (isset($execOut[$i]) && self::checkDigitalVersion($execOut[$i])) {
                        return $execOut[$i];
                    }
                }
            } else {
                return false;
            }
        }

        return false;
    }
}