<?php

namespace app\installapi\controller;

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
     * 连接命令行成功标识
     * @var string
     */
    protected $linkSuccessful = 'link-successful';

    /**
     * 命令执行完成标识
     * @var string
     */
    protected $commandExecutionCompleted = 'command-execution-completed';

    /**
     * 当前执行的命令,$command 的 key
     * @var string
     */
    protected $CurrentCommandKey = '';

    /**
     * 对可以执行的命令进行限制
     * @var string[]
     */
    protected $command = [
        'ping-baidu'   => 'ping baidu.com',
        'cnpm-install' => 'cnpm install',
        'npm-v'        => 'npm -v',
        'cnpm-v'       => 'cnpm -v',
        'node-v'       => 'node -v',
        'install-cnpm' => 'npm install -g cnpm --registry=https://registry.npmmirror.com',
        'test-install' => 'cd npm-install-test && cnpm install',
        'web-install'  => 'cd ../web && cnpm install',
        'web-build'    => 'cd ../web && cnpm run build:online',
    ];

    /**
     * 初始化
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $key
     * @param bool   $outputError
     * @return string
     */
    protected function getCommand($key, $outputError = true): string
    {
        if (!$key || !array_key_exists($key, $this->command)) {
            if ($outputError) {
                $this->output('Error: Command not allowed');
                $this->output($this->commandExecutionCompleted);
            }
            return false;
        }

        $this->CurrentCommandKey = $key;
        return $this->command[$key];
    }

    /**
     * 命令执行窗口的api
     */
    public function popenWindow()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        ob_end_flush();
        ob_implicit_flush(1);// 开启绝对刷新

        $command = $this->getCommand(request()->param('command'));
        if (!$command) return;

        $this->output($this->linkSuccessful);
        $this->output('> ' . $command);
        if (ob_get_level() == 0) ob_start();
        $handle = popen($command . ' 2>&1', 'r');
        while (!feof($handle)) {
            $this->output(fgets($handle));
            @ob_flush();// 刷新浏览器缓冲区
        }
        pclose($handle);
        $this->output($this->commandExecutionCompleted);
    }

    /**
     * 输出 EventSource 数据
     * @param $data
     */
    public function output($data, $callback = true)
    {
        $data = $this->filterColorMark($data);
        $data = str_replace(["\r\n", "\r", "\n"], "", $data);
        if ($data) {
            echo 'data: ' . mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5') . "\n\n";
            if ($callback) $this->outputCallback($data);
        }
    }

    public function filterColorMark($str)
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
        if ($this->CurrentCommandKey == 'test-install' || $this->CurrentCommandKey == 'web-install') {
            // 检测命令执行成功还是失败
            if (strpos(strtolower($output), 'all packages installed') !== false) {
                $this->output($this->CurrentCommandKey . '-success', false);
            }
        } else if ($this->CurrentCommandKey == 'install-cnpm') {
            $preg  = "/added ([0-9]*) packages in/i";
            $preg2 = "/added ([0-9]*) packages, removed/i";
            if (preg_match($preg, $output)) {
                $this->output('install-cnpm-success', false);
            }
            if (preg_match($preg2, $output)) {
                $this->output('install-cnpm-success', false);
            }
        } else if ($this->CurrentCommandKey == 'web-build') {
            if (strpos(strtolower($output), 'build successfully!') !== false) {
                $this->output($this->CurrentCommandKey . '-success', false);
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
        if (!$command) return false;

        $res    = [];
        $handle = popen($command . ' 2>&1', 'r');
        while (!feof($handle)) {
            $res[] = fgets($handle);
        }
        pclose($handle);
        return $res;
    }
}