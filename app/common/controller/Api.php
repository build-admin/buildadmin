<?php

namespace app\common\controller;

use Throwable;
use think\App;
use think\Response;
use app\BaseController;
use think\facade\Config;
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
        if ($this->useSystemSettings) {
            // ip检查
            ip_check();
            // 时区设定
            set_timezone();
        }

        parent::initialize();

        /**
         * 设置默认过滤规则
         * @see filter()
         */
        $this->request->filter('filter');

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
    protected function success(string $msg = '', mixed $data = null, int $code = 1, string $type = null, array $header = [], array $options = [])
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
    protected function error(string $msg = '', mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = [])
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
    public function result(string $msg, mixed $data = null, int $code = 0, string $type = null, array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];

        // 如果未设置类型则自动判断
        $type = $type ?: ($this->request->param(Config::get('route.var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        $code = 200;
        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        }

        $response = Response::create($result, $type, $code)->header($header)->options($options);
        throw new HttpResponseException($response);
    }

}