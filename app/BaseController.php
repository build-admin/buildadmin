<?php
declare (strict_types=1);

namespace app;

use think\App;
use think\Request;
use think\Validate;
use think\exception\ValidateException;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var Request
     */
    protected Request $request;

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected array $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(protected App $app)
    {
        $this->request                 = $this->app->request;
        $this->request->controllerPath = str_replace('.', '/', $this->request->controller(true));

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     * @access protected
     */
    protected function initialize(): void
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param array|string $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, array|string $validate, array $message = [], bool $batch = false): bool|array|string
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch();
        }

        return $v->failException()->check($data);
    }

}
