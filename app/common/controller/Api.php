<?php

namespace app\common\controller;

use Throwable;
use think\App;
use think\Response;
use think\facade\Db;
use app\BaseController;
use think\db\exception\PDOException;
use think\exception\HttpResponseException;

/**
 * API控制器基类
 */
class Api extends BaseController
{
    /**
     * 默认响应输出类型,支持json/xml/jsonp
     * @var string
     */
    protected string $responseType = 'json';

    /**
     * 应用站点系统设置
     * @var bool
     */
    protected bool $useSystemSettings = true;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 控制器初始化方法
     * @access protected
     * @throws Throwable
     */
    protected function initialize(): void
    {
        // 系统站点配置
        if ($this->useSystemSettings) {
            // 检查数据库连接
            try {
                Db::execute("SELECT 1");
            } catch (PDOException $e) {
                $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
            }

            ip_check(); // ip检查
            set_timezone(); // 时区设定
        }

        parent::initialize();

        // 加载控制器语言包
        $langSet = $this->app->lang->getLangSet();
        $this->app->lang->load([
            app_path() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . (str_replace('/', DIRECTORY_SEPARATOR, $this->app->request->controllerPath)) . '.php'
        ]);
    }

    /**
     * 操作成功
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function success(string $msg = '', mixed $data = null, int $code = 1, ?string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 操作失败
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    protected function error(string $msg = '', mixed $data = null, int $code = 0, ?string $type = null, array $header = [], array $options = []): void
    {
        $this->result($msg, $data, $code, $type, $header, $options);
    }

    /**
     * 返回 API 数据
     * @param string      $msg     提示消息
     * @param mixed       $data    返回数据
     * @param int         $code    错误码
     * @param string|null $type    输出类型
     * @param array       $header  发送的 header 信息
     * @param array       $options Response 输出参数
     */
    public function result(string $msg, mixed $data = null, int $code = 0, ?string $type = null, array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];

        $type = $type ?: $this->responseType;
        $code = $header['statusCode'] ?? 200;

        $response = Response::create($result, $type, $code)->header($header)->options($options);
        throw new HttpResponseException($response);
    }

}
