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
    protected string $title = '';

    /**
     * 自定义日志内容
     * @var string|array
     */
    protected string|array $data = '';

    /**
     * 忽略的链接正则列表
     * @var array
     */
    protected array $urlIgnoreRegex = [
        '/^(.*)\/(select|index|logout)$/i',
    ];

    protected array $desensitizationRegex = [
        '/(password|salt|token)/i'
    ];

    public static function instance()
    {
        $request = request();
        if (!isset($request->adminLog)) {
            $request->adminLog = new static();
        }
        return $request->adminLog;
    }

    /**
     * 设置标题
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * 设置日志内容
     * @param string|array $data
     */
    public function setData(string|array $data): void
    {
        $this->data = $data;
    }

    /**
     * 设置忽略的链接正则列表
     * @param array|string $regex
     */
    public function setUrlIgnoreRegex(array|string $regex = []): void
    {
        $regex                = is_array($regex) ? $regex : [$regex];
        $this->urlIgnoreRegex = array_merge($this->urlIgnoreRegex, $regex);
    }

    /**
     * 设置需要进行数据脱敏的正则列表
     * @param array|string $regex
     */
    public function setDesensitizationRegex(array|string $regex = []): void
    {
        $regex                      = is_array($regex) ? $regex : [$regex];
        $this->desensitizationRegex = array_merge($this->desensitizationRegex, $regex);
    }

    /**
     * 数据脱敏（只数组，根据数组 key 脱敏）
     * @param array|string $data
     * @return array|string
     */
    protected function desensitization(array|string $data): array|string
    {
        if (!is_array($data) || !$this->desensitizationRegex) {
            return $data;
        }
        foreach ($data as $index => &$item) {
            foreach ($this->desensitizationRegex as $reg) {
                if (preg_match($reg, $index)) {
                    $item = "***";
                } elseif (is_array($item)) {
                    $item = $this->desensitization($item);
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
    public function record(string $title = '', string|array|null $data = null): void
    {
        $auth     = Auth::instance();
        $adminId  = $auth->isLogin() ? $auth->id : 0;
        $username = $auth->isLogin() ? $auth->username : request()->param('username', __('Unknown'));

        $controller = str_replace('.', '/', request()->controller(true));
        $action     = request()->action(true);
        $path       = $controller . '/' . $action;
        if ($this->urlIgnoreRegex) {
            foreach ($this->urlIgnoreRegex as $item) {
                if (preg_match($item, $path)) {
                    return;
                }
            }
        }
        $data = $data ?: $this->data;
        if (!$data) {
            $data = request()->param('', null, 'trim,strip_tags,htmlspecialchars');
        }
        $data  = $this->desensitization($data);
        $title = $title ?: $this->title;
        if (!$title) {
            $controllerTitle = AdminRule::where('name', $controller)->value('title');
            $title           = AdminRule::where('name', $path)->value('title');
            $title           = $title ?: __('Unknown') . '(' . $action . ')';
            $title           = $controllerTitle ? ($controllerTitle . '-' . $title) : $title;
        }
        self::create([
            'admin_id'  => $adminId,
            'username'  => $username,
            'url'       => substr(request()->url(), 0, 1500),
            'title'     => $title,
            'data'      => !is_scalar($data) ? json_encode($data) : $data,
            'ip'        => request()->ip(),
            'useragent' => substr(request()->server('HTTP_USER_AGENT'), 0, 255),
        ]);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}