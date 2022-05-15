<?php

namespace ba;

use think\Response;
use think\facade\Config;
use think\facade\Cookie;
use app\admin\library\Auth;
use think\exception\HttpResponseException;

/**
 * 命令执行类
 */
class CommandExec
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 结果输出扩展
     * 每次命令执行有输出时,同时携带扩展数据
     */
    protected $outputExtend = null;

    /**
     * 状态标识
     */
    protected $flag = [
        // 连接成功
        'link-success'   => 'command-link-success',
        // 执行成功
        'exec-success'   => 'command-exec-success',
        // 执行完成 - 执行完成但未成功则为失败
        'exec-completed' => 'command-exec-completed',
        // 执行出错 - 不区分命令
        'exec-error'     => 'command-exec-error',
    ];

    /**
     * 当前执行的命令,$command 的 key
     * @var string
     */
    protected $currentCommandKey = '';

    /**
     * 对可以执行的命令进行限制
     * @var string[]
     */
    protected $command = [];

    /**
     * 自动构建的前端文件的 outDir（相对于根目录）
     */
    protected $distDir = 'dist';

    public function __construct($authentication)
    {
        set_time_limit(120);
        if ($authentication) {
            $token = request()->server('HTTP_BATOKEN', request()->request('batoken', Cookie::get('batoken') ?: false));
            $auth  = Auth::instance();
            $auth->init($token);
            if (!$auth->isLogin()) {
                $this->output('Error: Please login first');
                $this->outputFlag('exec-error');
                $this->break();
            }
        }
        $this->command = Config::get('buildadmin.allowed_commands');
    }

    /**
     * 初始化
     */
    public static function instance($authentication = true)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($authentication);
        }

        return self::$instance;
    }

    /**
     * 获取命令
     * @param string $key         命令key
     * @param bool   $outputError 是否直接输出错误
     * @return string
     */
    protected function getCommand(string $key, bool $outputError = true): string
    {
        if (!$key || !array_key_exists($key, $this->command)) {
            if ($outputError) {
                $this->output('Error: Command not allowed');
                $this->outputFlag('exec-error');
            }
            $this->break();
        }

        $this->currentCommandKey = $key;
        return $this->command[$key];
    }

    /**
     * 终端
     */
    public function terminal()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        ob_end_flush();
        ob_implicit_flush(1);// 开启绝对刷新

        $this->outputExtend = request()->param('extend');
        $command            = $this->getCommand(request()->param('command'));

        $this->outputFlag('link-success');
        $this->output('> ' . $command, false);
        if (ob_get_level() == 0) ob_start();
        $handle = popen($command . ' 2>&1', 'r');
        while (!feof($handle)) {
            $this->output(fgets($handle));
            @ob_flush();// 刷新浏览器缓冲区
        }
        pclose($handle);
        $this->outputFlag('exec-completed');
    }

    /**
     * 输出 EventSource 数据
     * @param string $data
     * @param bool   $callback
     */
    public function output(string $data, bool $callback = true)
    {
        $data = $this->filterMark($data);
        $data = str_replace(["\r\n", "\r", "\n"], "", $data);
        $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        $data = [
            'data'   => $data,
            'extend' => $this->outputExtend,
            'key'    => $this->currentCommandKey,
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data) {
            echo 'data: ' . $data . "\n\n";
            if ($callback) $this->outputCallback($data);
        }
    }

    /**
     * 输出状态标记
     * @param string $flag
     * @param string $command
     * @param bool   $callback
     */
    public function outputFlag(string $flag)
    {
        $this->output($this->flag[$flag], false);
    }

    public function filterMark($str)
    {
        $preg = '/\[(.*?)m/i';
        $str  = preg_replace($preg, '', $str);
        return $str;
    }

    public function filterASCII($str)
    {
        if (!$str) return '';
        $newStr = '';
        for ($i = 0; isset($str[$i]); $i++) {
            $ascCode = ord($str[$i]);
            if ($ascCode > 31 && $ascCode != 127) {
                $newStr .= $str[$i];
            }
        }
        return $newStr;
    }

    /**
     * 输出检测,检测命令执行状态等操作
     * @param $output
     */
    public function outputCallback($output)
    {
        if ($this->currentCommandKey == 'test-install' || $this->currentCommandKey == 'web-install') {
            if (strpos(strtolower($output), 'all packages installed') !== false) {
                $this->outputFlag('exec-success');
            }
        } elseif ($this->currentCommandKey == 'install-cnpm') {
            $preg  = "/added ([0-9]*) packages in/i";
            $preg2 = "/added ([0-9]*) packages, removed/i";
            $preg3 = "/removed ([0-9]*) packages, and changed ([0-9]*) packages in/i";
            if (preg_match($preg, $output) || preg_match($preg2, $output) || preg_match($preg3, $output)) {
                // 获取一次cnpm版本号
                if (Version::getCnpmVersion()) {
                    $this->outputFlag('exec-success');
                }
            }
        } elseif ($this->currentCommandKey == 'web-build') {
            if (strpos(strtolower($output), 'build successfully!') !== false) {
                if ($this->mvDist()) {
                    $this->outputFlag('exec-success');
                } else {
                    $this->output('Build succeeded, but move file failed. Please operate manually.');
                }
            }
        } elseif ($this->currentCommandKey == 'npm-v') {
            $preg = "/([0-9]+)\.([0-9]+)\.([0-9]+)/";
            if (preg_match($preg, $output)) {
                $this->outputFlag('exec-success');
            }
        }
    }

    /**
     * 执行一个命令并以字符串数组的方式返回执行输出
     * 代替 exec 使用，这样就只需要解除 popen 的函数禁用了
     * @param $commandKey
     * @return array | bool
     */
    public function getOutputFromPopen($commandKey)
    {
        if (!function_exists('popen') || !function_exists('pclose') || !function_exists('feof') || !function_exists('fgets')) {
            return false;
        }
        $command = $this->getCommand($commandKey, false);

        $res    = [];
        $handle = popen($command . ' 2>&1', 'r');
        while (!feof($handle)) {
            $res[] = fgets($handle);
        }
        pclose($handle);
        return $res;
    }

    public function break()
    {
        throw new HttpResponseException(Response::create()->contentType('text/event-stream'));
    }

    public function mvDist()
    {
        $distPath      = root_path() . $this->distDir . DIRECTORY_SEPARATOR;
        $indexHtmlPath = $distPath . 'index.html';
        $assetsPath    = $distPath . 'assets';
        if (!file_exists($indexHtmlPath) || !file_exists($assetsPath)) {
            return false;
        }

        $toIndexHtmlPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'index.html';
        $toAssetsPath    = root_path() . 'public' . DIRECTORY_SEPARATOR . 'assets';
        @unlink($toIndexHtmlPath);
        deldir($toAssetsPath);

        if (rename($indexHtmlPath, $toIndexHtmlPath) && rename($assetsPath, $toAssetsPath)) {
            deldir($distPath);
            return true;
        } else {
            return false;
        }
    }
}