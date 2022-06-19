<?php
// 应用公共文件

use think\facade\Db;

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
    /**
     * 语言翻译
     * @param string $name 被翻译字符
     * @param array  $vars 替换字符数组
     * @param string $lang 翻译语言
     * @return string
     */
    function __(string $name, array $vars = [], string $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        return \think\facade\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('get_sys_config')) {
    function get_sys_config($name = '')
    {
        if ($name) {
            $config = \app\admin\model\Config::where('name', $name)->find();
            if ($config) {
                return $config['value'];
            }
        } else {
            return \app\admin\model\Config::order('weigh desc')->select()->toArray();
        }
    }
}

if (!function_exists('get_route_remark')) {
    /**
     * 获取当前路由后台菜单规则的备注信息
     * @return string
     */
    function get_route_remark()
    {
        $controllername = request()->controller(true);
        $actionname     = request()->action(true);
        $path           = str_replace('.', '/', $controllername);

        $remark = \think\facade\Db::name('menu_rule')
            ->where('name', $path)
            ->whereOr('name', $path . '/' . $actionname)
            ->value('remark');
        return __((string)$remark);
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

if (!function_exists('encrypt_password')) {
    /**
     * 加密密码
     */
    function encrypt_password($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($encrypt($password) . $salt);
    }
}

if (!function_exists('action_in_arr')) {
    /**
     * 检测一个方法是否在传递的数组内
     * @return bool
     */
    function action_in_arr($arr = [])
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        if (in_array(strtolower(request()->action()), $arr) || in_array('*', $arr)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('build_suffix_svg')) {
    /**
     * 构建文件后缀的svg图片
     * @param string $suffix     文件后缀
     * @param string $background 背景颜色，如：rgb(255,255,255)
     * @return string
     */
    function build_suffix_svg($suffix = 'file', $background = null)
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total  = unpack('L', hash('adler32', $suffix, true))[1];
        $hue    = $total % 360;
        [$r, $g, $b] = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ? $background : "rgb({$r},{$g},{$b})";

        $icon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:' . $background . ';" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">' . $suffix . '</tspan></text></g>
        </svg>';
        return $icon;
    }
}

if (!function_exists('get_area')) {
    function get_area()
    {
        $province     = request()->get('province', '');
        $city         = request()->get('city', '');
        $where        = ['pid' => 0, 'level' => 1];
        $provincelist = null;
        if ($province !== '') {
            $where['pid']   = $province;
            $where['level'] = 2;
            if ($city !== '') {
                $where['pid']   = $city;
                $where['level'] = 3;
            }
        }
        $provincelist = Db::name('area')->where($where)->field('id as value,name as label')->select();
        return $provincelist;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}