<?php
// +----------------------------------------------------------------------
// | BuildAdmin [ Quickly create commercial-grade management system using popular technology stack ]
// +----------------------------------------------------------------------
// | Copyright (c) 2022~2022 http://buildadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 妙码生花 <hi@buildadmin.com>
// +----------------------------------------------------------------------

namespace ba;

use Throwable;
use think\Response;
use think\facade\Config;
use think\facade\Cookie;
use app\admin\library\Auth;
use app\admin\library\module\Manage;
use think\exception\HttpResponseException;

class Terminal
{
    /**
     * @var ?Terminal 对象实例
     */
    protected static ?Terminal $instance = null;

    /**
     * @var string 当前执行的命令 $command 的 key
     */
    protected string $commandKey = '';

    /**
     * @var array proc_open 的参数
     */
    protected array $descriptorsPec = [];

    /**
     * @var resource|bool proc_open 返回的 resource
     */
    protected $process = false;

    /**
     * @var array proc_open 的管道
     */
    protected array $pipes = [];

    /**
     * @var int proc 执行状态
     */
    protected int $procStatus = 0;

    /**
     * @var string 命令在前台的uuid
     */
    protected string $uuid = '';

    /**
     * @var string 扩展信息
     */
    protected string $extend = '';

    /**
     * @var string 命令执行输出文件
     */
    protected string $outputFile = '';

    /**
     * @var string 命令执行实时输出内容
     */
    protected string $outputContent = '';

    /**
     * @var string 自动构建的前端文件的 outDir（相对于根目录）
     */
    protected static string $distDir = 'web' . DIRECTORY_SEPARATOR . 'dist';

    /**
     * @var array 状态标识
     */
    protected array $flag = [
        // 连接成功
        'link-success'   => 'command-link-success',
        // 执行成功
        'exec-success'   => 'command-exec-success',
        // 执行完成
        'exec-completed' => 'command-exec-completed',
        // 执行出错
        'exec-error'     => 'command-exec-error',
    ];

    /**
     * 初始化
     */
    public static function instance(): Terminal
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->uuid   = request()->param('uuid', '');
        $this->extend = request()->param('extend', '');

        // 初始化日志文件
        $outputDir        = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'terminal';
        $this->outputFile = $outputDir . DIRECTORY_SEPARATOR . 'exec.log';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        file_put_contents($this->outputFile, '');

        /**
         * 命令执行结果输出到文件而不是管道
         * 因为输出到管道时有延迟，而文件虽然需要频繁读取和对比内容，但是输出实时的
         */
        $this->descriptorsPec = [0 => ['pipe', 'r'], 1 => ['file', $this->outputFile, 'w'], 2 => ['file', $this->outputFile, 'w']];
    }

    /**
     * 获取命令
     * @param string $key 命令key
     * @return array|bool
     */
    public static function getCommand(string $key): bool|array
    {
        if (!$key) {
            return false;
        }

        $commands = Config::get('terminal.commands');
        if (stripos($key, '.')) {
            $key = explode('.', $key);
            if (!array_key_exists($key[0], $commands) || !is_array($commands[$key[0]]) || !array_key_exists($key[1], $commands[$key[0]])) {
                return false;
            }
            $command = $commands[$key[0]][$key[1]];
        } else {
            if (!array_key_exists($key, $commands)) {
                return false;
            }
            $command = $commands[$key];
        }
        if (!is_array($command)) {
            $command = [
                'cwd'     => root_path(),
                'command' => $command,
            ];
        } else {
            $command = [
                'cwd'     => root_path() . $command['cwd'],
                'command' => $command['command'],
            ];
        }
        $command['cwd'] = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $command['cwd']);
        return $command;
    }

    /**
     * 执行命令
     * @param bool $authentication 是否鉴权
     * @throws Throwable
     */
    public function exec(bool $authentication = true): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $this->commandKey = request()->param('command');

        if (ob_get_level()) ob_end_clean();
        if (!ob_get_level()) ob_start();

        $command = self::getCommand($this->commandKey);
        if (!$command) {
            $this->execError('The command was not allowed to be executed', true);
        }

        if ($authentication) {
            $token = request()->server('HTTP_BATOKEN', request()->request('batoken', Cookie::get('batoken') ?: false));
            $auth  = Auth::instance();
            $auth->init($token);
            if (!$auth->isSuperAdmin()) {
                $this->execError("You're not super administrator", true);
            }
        }

        $this->beforeExecution();
        $this->outputFlag('link-success');
        $this->output('> ' . $command['command'], false);

        $this->process = proc_open($command['command'], $this->descriptorsPec, $this->pipes, $command['cwd']);
        if (!is_resource($this->process)) {
            $this->execError('Failed to execute', true);
        }
        while ($this->getProcStatus()) {
            $contents = file_get_contents($this->outputFile);
            if (strlen($contents) && $this->outputContent != $contents) {
                $newOutput = str_replace($this->outputContent, '', $contents);
                if (preg_match('/\r\n|\r|\n/', $newOutput)) {
                    $this->output($newOutput);
                    $this->outputContent = $contents;
                }
            }
            usleep(500000);
        }
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($this->process);
        $this->outputFlag('exec-completed');
    }

    /**
     * 获取执行状态
     * @throws Throwable
     */
    public function getProcStatus(): bool
    {
        $status = proc_get_status($this->process);
        if ($status['running']) {
            $this->procStatus = 1;
            return true;
        } elseif ($this->procStatus === 1) {
            $this->procStatus = 0;
            $this->output('exitcode: ' . $status['exitcode']);
            if ($status['exitcode'] === 0) {
                if ($this->successCallback()) {
                    $this->outputFlag('exec-success');
                } else {
                    $this->output('Error: Command execution succeeded, but callback execution failed');
                    $this->outputFlag('exec-error');
                }
            } else {
                $this->outputFlag('exec-error');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 输出 EventSource 数据
     * @param string $data
     * @param bool   $callback
     */
    public function output(string $data, bool $callback = true): void
    {
        $data = self::outputFilter($data);
        $data = [
            'data'   => $data,
            'uuid'   => $this->uuid,
            'extend' => $this->extend,
            'key'    => $this->commandKey,
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data) {
            echo 'data: ' . $data . "\n\n";
            if ($callback) $this->outputCallback($data);
            @ob_flush();// 刷新浏览器缓冲区
        }
    }

    /**
     * 输出状态标记
     * @param string $flag
     */
    public function outputFlag(string $flag): void
    {
        $this->output($this->flag[$flag], false);
    }

    /**
     * 输出后回调
     */
    public function outputCallback($data): void
    {

    }

    /**
     * 成功后回调
     * @return bool
     * @throws Throwable
     */
    public function successCallback(): bool
    {
        if (stripos($this->commandKey, '.')) {
            $commandKeyArr = explode('.', $this->commandKey);
            $commandPKey   = $commandKeyArr[0] ?? '';
        } else {
            $commandPKey = $this->commandKey;
        }

        if ($commandPKey == 'web-build') {
            if (!self::mvDist()) {
                $this->output('Build succeeded, but move file failed. Please operate manually.');
                return false;
            }
        } elseif ($commandPKey == 'web-install' && $this->extend) {
            [$type, $value] = explode(':', $this->extend);
            if ($type == 'module-install' && $value) {
                Manage::instance($value)->dependentInstallComplete('npm');
            }
        } elseif ($commandPKey == 'composer' && $this->extend) {
            [$type, $value] = explode(':', $this->extend);
            if ($type == 'module-install' && $value) {
                Manage::instance($value)->dependentInstallComplete('composer');
            }
        } elseif ($commandPKey == 'nuxt-install' && $this->extend) {
            [$type, $value] = explode(':', $this->extend);
            if ($type == 'module-install' && $value) {
                Manage::instance($value)->dependentInstallComplete('nuxt_npm');
            }
        }
        return true;
    }

    /**
     * 执行前埋点
     */
    public function beforeExecution(): void
    {
        if ($this->commandKey == 'test.pnpm') {
            @unlink(root_path() . 'public' . DIRECTORY_SEPARATOR . 'npm-install-test' . DIRECTORY_SEPARATOR . 'pnpm-lock.yaml');
        } elseif ($this->commandKey == 'web-install.pnpm') {
            @unlink(root_path() . 'web' . DIRECTORY_SEPARATOR . 'pnpm-lock.yaml');
        }
    }

    /**
     * 输出过滤
     */
    public static function outputFilter($str): string
    {
        $str  = trim($str);
        $preg = '/\[(.*?)m/i';
        $str  = preg_replace($preg, '', $str);
        $str  = str_replace(["\r\n", "\r", "\n"], "\n", $str);
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
    }

    /**
     * 执行错误
     */
    public function execError($error, $break = false): void
    {
        $this->output('Error:' . $error);
        $this->outputFlag('exec-error');
        if ($break) $this->break();
    }

    /**
     * 退出执行
     */
    public function break(): void
    {
        throw new HttpResponseException(Response::create()->contentType('text/event-stream'));
    }

    /**
     * 执行一个命令并以字符串的方式返回执行输出
     * 代替 exec 使用，这样就只需要解除 proc_open 的函数禁用了
     * @param $commandKey
     * @return string|bool
     */
    public static function getOutputFromProc($commandKey): bool|string
    {
        if (!function_exists('proc_open') || !function_exists('proc_close')) {
            return false;
        }
        $command = self::getCommand($commandKey);
        if (!$command) {
            return false;
        }
        $descriptorsPec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process        = proc_open($command['command'], $descriptorsPec, $pipes, null, null);
        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            $info .= stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return self::outputFilter($info);
        }
        return '';
    }

    public static function mvDist(): bool
    {
        $distPath      = root_path() . self::$distDir . DIRECTORY_SEPARATOR;
        $indexHtmlPath = $distPath . 'index.html';
        $assetsPath    = $distPath . 'assets';
        if (!file_exists($indexHtmlPath) || !file_exists($assetsPath)) {
            return false;
        }

        $toIndexHtmlPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'index.html';
        $toAssetsPath    = root_path() . 'public' . DIRECTORY_SEPARATOR . 'assets';
        @unlink($toIndexHtmlPath);
        Filesystem::delDir($toAssetsPath);

        if (rename($indexHtmlPath, $toIndexHtmlPath) && rename($assetsPath, $toAssetsPath)) {
            Filesystem::delDir($distPath);
            return true;
        } else {
            return false;
        }
    }

    public static function changeTerminalConfig($config = []): bool
    {
        // 不保存在数据库中，因为切换包管理器时，数据库资料可能还未配置
        $oldPort           = Config::get('terminal.install_service_port');
        $oldPackageManager = Config::get('terminal.npm_package_manager');
        $newPort           = request()->post('port', $config['port'] ?? $oldPort);
        $newPackageManager = request()->post('manager', $config['manager'] ?? $oldPackageManager);

        if ($oldPort == $newPort && $oldPackageManager == $newPackageManager) {
            return true;
        }

        $buildConfigFile    = config_path() . 'terminal.php';
        $buildConfigContent = @file_get_contents($buildConfigFile);
        $buildConfigContent = preg_replace("/'install_service_port'(\s+)=>(\s+)'$oldPort'/", "'install_service_port'\$1=>\$2'$newPort'", $buildConfigContent);
        $buildConfigContent = preg_replace("/'npm_package_manager'(\s+)=>(\s+)'$oldPackageManager'/", "'npm_package_manager'\$1=>\$2'$newPackageManager'", $buildConfigContent);
        $result             = @file_put_contents($buildConfigFile, $buildConfigContent);
        return (bool)$result;
    }
}