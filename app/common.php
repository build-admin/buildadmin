<?php
// 应用公共文件

use think\App;
use ba\Filesystem;
use think\Response;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Config;
use voku\helper\AntiXSS;
use app\admin\model\Config as configModel;
use think\exception\HttpResponseException;
use Symfony\Component\HttpFoundation\IpUtils;

if (!function_exists('__')) {

    /**
     * 语言翻译
     * @param string $name 被翻译字符
     * @param array  $vars 替换字符数组
     * @param string $lang 翻译语言
     * @return mixed
     */
    function __(string $name, array $vars = [], string $lang = ''): mixed
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('filter')) {

    /**
     * 输入过滤
     * 富文本反XSS请使用 clean_xss，也就不需要及不能再 filter 了
     * @param string $string 要过滤的字符串
     * @return string
     */
    function filter(string $string): string
    {
        // 去除字符串两端空格（对防代码注入有一定作用）
        $string = trim($string);

        // 过滤html和php标签
        $string = strip_tags($string);

        // 特殊字符转实体
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}

if (!function_exists('clean_xss')) {

    /**
     * 清理XSS
     * 通常只用于富文本，比 filter 慢
     * @param string $string
     * @return string
     */
    function clean_xss(string $string): string
    {
        return (new AntiXSS())->xss_clean($string);
    }
}

if (!function_exists('htmlspecialchars_decode_improve')) {
    /**
     * html解码增强
     * 被 filter函数 内的 htmlspecialchars 编码的字符串，需要用此函数才能完全解码
     * @param string $string
     * @param int    $flags
     * @return string
     */
    function htmlspecialchars_decode_improve(string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401): string
    {
        return htmlspecialchars_decode($string, $flags);
    }
}

if (!function_exists('get_sys_config')) {

    /**
     * 获取站点的系统配置，不传递参数则获取所有配置项
     * @param string $name    变量名
     * @param string $group   变量分组，传递此参数来获取某个分组的所有配置项
     * @param bool   $concise 是否开启简洁模式，简洁模式下，获取多项配置时只返回配置的键值对
     * @return mixed
     * @throws Throwable
     */
    function get_sys_config(string $name = '', string $group = '', bool $concise = true): mixed
    {
        if ($name) {
            // 直接使用->value('value')不能使用到模型的类型格式化
            $config = configModel::cache($name, null, configModel::$cacheTag)->where('name', $name)->find();
            if ($config) $config = $config['value'];
        } else {
            if ($group) {
                $temp = configModel::cache('group' . $group, null, configModel::$cacheTag)->where('group', $group)->select()->toArray();
            } else {
                $temp = configModel::cache('sys_config_all', null, configModel::$cacheTag)->order('weigh desc')->select()->toArray();
            }
            if ($concise) {
                $config = [];
                foreach ($temp as $item) {
                    $config[$item['name']] = $item['value'];
                }
            } else {
                $config = $temp;
            }
        }
        return $config;
    }
}

if (!function_exists('get_route_remark')) {

    /**
     * 获取当前路由后台菜单规则的备注信息
     * @return string
     */
    function get_route_remark(): string
    {
        $controllerName = request()->controller(true);
        $actionName     = request()->action(true);
        $path           = str_replace('.', '/', $controllerName);

        $remark = Db::name('admin_rule')
            ->where('name', $path)
            ->whereOr('name', $path . '/' . $actionName)
            ->value('remark');
        return __((string)$remark);
    }
}

if (!function_exists('full_url')) {

    /**
     * 获取资源完整url地址；若安装了云存储或 config/buildadmin.php 配置了CdnUrl，则自动使用对应的CdnUrl
     * @param string      $relativeUrl 资源相对地址 不传入则获取域名
     * @param string|bool $domain      是否携带域名 或者直接传入域名
     * @param string      $default     默认值
     * @return string
     */
    function full_url(string $relativeUrl = '', string|bool $domain = true, string $default = ''): string
    {
        // 存储/上传资料配置
        Event::trigger('uploadConfigInit', App::getInstance());

        $cdnUrl = Config::get('buildadmin.cdn_url');
        if (!$cdnUrl) $cdnUrl = request()->upload['cdn'] ?? '//' . request()->host();
        if ($domain === true) {
            $domain = $cdnUrl;
        } elseif ($domain === false) {
            $domain = '';
        }

        $relativeUrl = $relativeUrl ?: $default;
        if (!$relativeUrl) return $domain;

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $relativeUrl) || preg_match($regex, $relativeUrl) || $domain === false) {
            return $relativeUrl;
        }
        return $domain . $relativeUrl;
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

if (!function_exists('str_attr_to_array')) {

    /**
     * 将字符串属性列表转为数组
     * @param string $attr 属性，一行一个，无需引号，比如：class=input-class
     * @return array
     */
    function str_attr_to_array(string $attr): array
    {
        if (!$attr) return [];
        $attr     = explode("\n", trim(str_replace("\r\n", "\n", $attr)));
        $attrTemp = [];
        foreach ($attr as $item) {
            $item = explode('=', $item);
            if (isset($item[0]) && isset($item[1])) {
                $attrVal = $item[1];
                if ($item[1] === 'false' || $item[1] === 'true') {
                    $attrVal = !($item[1] === 'false');
                } elseif (is_numeric($item[1])) {
                    $attrVal = (float)$item[1];
                }
                if (strpos($item[0], '.')) {
                    $attrKey = explode('.', $item[0]);
                    if (isset($attrKey[0]) && isset($attrKey[1])) {
                        $attrTemp[$attrKey[0]][$attrKey[1]] = $attrVal;
                        continue;
                    }
                }
                $attrTemp[$item[0]] = $attrVal;
            }
        }
        return $attrTemp;
    }
}

if (!function_exists('action_in_arr')) {

    /**
     * 检测一个方法是否在传递的数组内
     * @param array $arr
     * @return bool
     */
    function action_in_arr(array $arr = []): bool
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
     * @param string  $suffix     文件后缀
     * @param ?string $background 背景颜色，如：rgb(255,255,255)
     * @return string
     */
    function build_suffix_svg(string $suffix = 'file', string $background = null): string
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total  = unpack('L', hash('adler32', $suffix, true))[1];
        $hue    = $total % 360;
        [$r, $g, $b] = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ?: "rgb($r,$g,$b)";

        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:' . $background . ';" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">' . $suffix . '</tspan></text></g>
        </svg>';
    }
}

if (!function_exists('get_area')) {

    /**
     * 获取省份地区数据
     * @throws Throwable
     */
    function get_area(): array
    {
        $province = request()->get('province', '');
        $city     = request()->get('city', '');
        $where    = ['pid' => 0, 'level' => 1];
        if ($province !== '') {
            $where['pid']   = $province;
            $where['level'] = 2;
            if ($city !== '') {
                $where['pid']   = $city;
                $where['level'] = 3;
            }
        }
        return Db::name('area')
            ->where($where)
            ->field('id as value,name as label')
            ->select()
            ->toArray();
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v): array
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

if (!function_exists('ip_check')) {

    /**
     * IP检查
     * @throws Throwable
     */
    function ip_check($ip = null): void
    {
        $ip       = is_null($ip) ? request()->ip() : $ip;
        $noAccess = get_sys_config('no_access_ip');
        $noAccess = !$noAccess ? [] : array_filter(explode("\n", str_replace("\r\n", "\n", $noAccess)));
        if ($noAccess && IpUtils::checkIp($ip, $noAccess)) {
            $response = Response::create(['msg' => 'No permission request'], 'json', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('set_timezone')) {

    /**
     * 设置时区
     * @throws Throwable
     */
    function set_timezone($timezone = null): void
    {
        $defaultTimezone = Config::get('app.default_timezone');
        $timezone        = is_null($timezone) ? get_sys_config('time_zone') : $timezone;
        if ($timezone && $defaultTimezone != $timezone) {
            Config::set([
                'app.default_timezone' => $timezone
            ]);
            date_default_timezone_set($timezone);
        }
    }
}

if (!function_exists('get_upload_config')) {

    /**
     * 获取上传配置
     * @return array
     */
    function get_upload_config(): array
    {
        // 存储/上传资料配置
        Event::trigger('uploadConfigInit', App::getInstance());

        $uploadConfig            = Config::get('upload');
        $uploadConfig['maxsize'] = Filesystem::fileUnitToByte($uploadConfig['maxsize']);

        $upload = request()->upload;
        if (!$upload) {
            $uploadConfig['mode'] = 'local';
            return $uploadConfig;
        }
        unset($upload['cdn']);
        return array_merge($upload, $uploadConfig);
    }
}

if (!function_exists('get_auth_token')) {

    /**
     * 获取鉴权 token
     * @param array $names
     * @return string
     */
    function get_auth_token(array $names = ['ba', 'token']): string
    {
        $separators = [
            'header' => ['', '-'], // batoken、ba-token【ba_token 不在 header 的接受列表内因为兼容性不高，改用 http_ba_token】
            'param'  => ['', '-', '_'], // batoken、ba-token、ba_token
            'server' => ['_'], // http_ba_token
        ];

        $tokens  = [];
        $request = request();
        foreach ($separators as $fun => $sps) {
            foreach ($sps as $sp) {
                $tokens[] = $request->$fun(($fun == 'server' ? 'http_' : '') . implode($sp, $names));
            }
        }
        $tokens = array_filter($tokens);
        return array_values($tokens)[0] ?? '';
    }
}
