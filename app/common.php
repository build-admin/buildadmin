<?php
// 应用公共文件

if (!function_exists('path_is_writable')) {
    /**
     * 检查目录/文件是否可写
     * @param $path
     * @return bool
     */
    function path_is_writable($path): bool
    {
        if (DIRECTORY_SEPARATOR == '/' && @ini_get('safe_mode') == false) {
            return is_writable($path);
        }

        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));
            if (($fp = @fopen($path, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            @chmod($path, 0777);
            @unlink($path);

            return true;
        } elseif (!is_file($path) || ($fp = @fopen($path, 'ab')) === false) {
            return false;
        }

        fclose($fp);
        return true;
    }
}

if (!function_exists('deldir')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool   $delself 是否删除自身
     * @return boolean
     */
    function deldir($dirname, $delself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                deldir($fileinfo->getRealPath(), true);
            } else {
                @unlink($fileinfo->getRealPath());
            }
        }
        if ($delself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('__')) {
    function __(string $name, array $vars = [], string $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        return \think\facade\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('get_route_remark')) {
    function get_route_remark()
    {
        $controllername = request()->controller(true);
        $actionname     = request()->action(true);
        $path           = str_replace('.', '/', $controllername) . '/' . $actionname;
        return \think\facade\Db::name('menu_rule')->where('name', $path)->value('remark');
    }
}

if (!function_exists('full_url')) {
    /**
     * 获取资源完整url地址
     * @param string  $relativeUrl 资源相对地址 不传入则获取域名
     * @param boolean $domain      是否携带域名 或者直接传入域名
     * @return string
     */
    function full_url($relativeUrl = false, $domain = true, $default = '')
    {
        $relativeUrl = $relativeUrl ? $relativeUrl : $default;
        if (!$relativeUrl) {
            return $domain === true ? request()->domain() : $domain;
        }
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $relativeUrl) || preg_match($regex, $relativeUrl) || $domain === false) {
            return $relativeUrl;
        }
        return $domain === true ? request()->domain() . $relativeUrl : $domain . $relativeUrl;
    }
}