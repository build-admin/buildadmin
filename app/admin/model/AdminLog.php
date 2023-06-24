<?php

namespace app\admin\model;

use Throwable;
use think\Model;
use app\admin\library\Auth;
use think\model\relation\BelongsTo;

/**
 * AdminLog模型
 */
class AdminLog extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    /**
     * 自定义日志标题
     * @var string
     */
    protected static string $title = '';

    /**
     * 自定义日志内容
     * @var string|array
     */
    protected static string|array $data = '';

    /**
     * 忽略的链接正则列表
     * @var array
     */
    protected static array $urlIgnoreRegex = [
        '/^(.*)\/(select|index|logout)$/i',
    ];

    /**
     * 设置标题
     * @param string $title
     */
    public static function setTitle(string $title): void
    {
        self::$title = $title;
    }

    /**
     * 设置日志内容
     * @param string|array $data
     */
    public static function setData(string|array $data): void
    {
        self::$data = $data;
    }

    /**
     * 设置忽略的链接正则列表
     * @param array|string $regex
     */
    public static function setUrlIgnoreRegex(array|string $regex = []): void
    {
        $regex                = is_array($regex) ? $regex : [$regex];
        self::$urlIgnoreRegex = array_merge(self::$urlIgnoreRegex, $regex);
    }

    /**
     * 数据脱敏（只数组，根据数组 key 脱敏）
     * @param array|string $data
     * @return array|string
     */
    protected static function pureData(array|string $data): array|string
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

    /**
     * 写入日志
     * @param string            $title
     * @param string|array|null $data
     * @throws Throwable
     */
    public static function record(string $title = '', string|array $data = null): void
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
        }
        $data  = self::pureData($data);
        $title = $title ?: self::$title;
        if (!$title) {
            $controllerTitle = AdminRule::where('name', $controller)->value('title');
            $title           = AdminRule::where('name', $path)->value('title');
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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}