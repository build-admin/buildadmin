<?php

namespace ba;


/**
 * 版本类
 */
class Version
{

    /**
     * 比较两个版本号
     * @param $v1 string 要求的版本号
     * @param $v2 bool | string 被比较版本号
     * @return bool 是否达到要求的版本号
     */
    public static function compare(string $v1, bool|string $v2): bool
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
        if (str_contains($v1, '-')) $v1 = explode('-', $v1)[0];
        if (str_contains($v2, '-')) $v2 = explode('-', $v2)[0];

        $v1 = explode('.', $v1);
        $v2 = explode('.', $v2);

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
        return false;
    }

    /**
     * 是否是一个数字版本号
     * @param $version
     * @return bool
     */
    public static function checkDigitalVersion($version): bool
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

    /**
     * @return string
     */
    public static function getCnpmVersion(): string
    {
        $execOut = Terminal::getOutputFromProc('version.cnpm');
        if ($execOut) {
            $preg = '/cnpm@(.+?) \(/is';
            preg_match($preg, $execOut, $result);
            return $result[1] ?? '';
        } else {
            return '';
        }
    }

    /**
     * 获取依赖版本号
     * @param string $name 支持：npm、cnpm、yarn、pnpm、node
     * @return string
     */
    public static function getVersion(string $name): string
    {
        if ($name == 'cnpm') {
            return self::getCnpmVersion();
        } elseif (in_array($name, ['npm', 'yarn', 'pnpm', 'node'])) {
            $execOut = Terminal::getOutputFromProc('version.' . $name);
            if ($execOut) {
                if (strripos($execOut, 'npm WARN') !== false) {
                    $preg = '/\d+(\.\d+){0,2}/';
                    preg_match($preg, $execOut, $matches);
                    if (isset($matches[0]) && self::checkDigitalVersion($matches[0])) {
                        return $matches[0];
                    }
                }
                $execOut = preg_split('/\r\n|\r|\n/', $execOut);
                // 检测两行，第一行可能会是个警告消息
                for ($i = 0; $i < 2; $i++) {
                    if (isset($execOut[$i]) && self::checkDigitalVersion($execOut[$i])) {
                        return $execOut[$i];
                    }
                }
            } else {
                return '';
            }
        }
        return '';
    }
}