<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

/**
 * AdminLog模型
 * @controllerUrl 'authAdminLog'
 */
class AdminLog extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 自定义日志标题
    protected static $title = '';
    // 自定义日志内容
    protected static $data = '';

    // 忽略的链接正则列表
    protected static $urlIgnoreRegex = [
        '/^(.*)\/(select|index|logout)$/i',
    ];

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setData($data)
    {
        self::$data = $data;
    }

    public static function setUrlIgnoreRegex($regex = [])
    {
        $regex                = is_array($regex) ? $regex : [$regex];
        self::$urlIgnoreRegex = array_merge(self::$urlIgnoreRegex, $regex);
    }

    /**
     * 数据脱敏
     * @param $data
     * @return array
     */
    protected static function pureData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $index => &$item) {
            if (preg_match("/(password|salt|token)/i", $index)) {
                $item = "***";
            } else {
                if (is_array($item)) {
                    $item = self::pureData($item);
                }
            }
        }
        return $data;
    }

    public static function record($title = '', $data = '')
    {
        $auth     = Auth::instance();
        $admin_id = $auth->isLogin() ? $auth->id : 0;
        $username = $auth->isLogin() ? $auth->username : __('Unknown');

        $controller = str_replace('.', '/', request()->controller(true));
        $action     = request()->action(true);
        $path       = $controller . '/' . $action;
        if (self::$urlIgnoreRegex) {
            foreach (self::$urlIgnoreRegex as $item) {
                if (preg_match($item, $path)) {
                    return;
                }
            }
        }
        $data = $data ?: self::$data;
        if (!$data) {
            $data = request()->param('', null, 'trim,strip_tags,htmlspecialchars');
            $data = self::pureData($data);
        }
        $title = $title ?: self::$title;
        if (!$title) {
            $controllerTitle = MenuRule::where('name', $controller)->value('title');
            $title           = MenuRule::where('name', $path)->value('title');
            $title           = $title ?: __('Unknown') . '(' . $action . ')';
            $title           = $controllerTitle ? ($controllerTitle . '-' . $title) : $title;
        }
        self::create([
            'admin_id'  => $admin_id,
            'username'  => $username,
            'url'       => substr(request()->url(), 0, 1500),
            'title'     => $title,
            'data'      => !is_scalar($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
            'ip'        => request()->ip(),
            'useragent' => substr(request()->server('HTTP_USER_AGENT'), 0, 255),
        ]);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}