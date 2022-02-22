<?php
declare (strict_types=1);

namespace app\installapi\controller;

use bd\Version;
use app\common\controller\Api;
use think\App;

class Index extends Api
{
    static $ok   = 'ok';
    static $fail = 'fail';
    static $warn = 'warn';

    static $lockFileName     = 'install.lock';
    static $dbConfigFileName = 'database.php';

    // 自动构建的前端文件的 outDir 相对于根目录
    static $distDir = 'dist';

    public function __construct(App $app)
    {
        parent::__construct($app);
        set_time_limit(120);
    }

    public function index()
    {
        return 'BuildAdmin-' . __('Install the controller');
    }

    public function envBaseCheck()
    {

        if (is_file(public_path() . self::$lockFileName)) {
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]), [], 3);
        }

        $phpVersion        = phpversion();
        $phpVersionCompare = Version::compare('7.1.0', $phpVersion);
        $dbConfigFile      = config_path() . self::$dbConfigFileName;
        $configIsWritable  = path_is_writable(config_path()) && path_is_writable($dbConfigFile);
        $publicIsWritable  = path_is_writable(public_path());
        $phpSafeMode       = @ini_get('safe_mode');
        $phpPopen          = function_exists('popen') && function_exists('pclose');
        $phpFileOperation  = function_exists('feof') && function_exists('fgets');
        $phpMysqli         = extension_loaded('mysqli') && extension_loaded("PDO");

        $this->success('ok', [
            'php_version'        => [
                'describe' => $phpVersion,
                'state'    => $phpVersionCompare ? self::$ok : self::$fail,
                'need'     => !$phpVersionCompare ? __('need') . ' >= 7.1.0' : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=PHP版本不足'
                ]
            ],
            'config_is_writable' => [
                'describe' => self::writableStateDescribe($configIsWritable),
                'state'    => $configIsWritable ? self::$ok : self::$fail,
                'need'     => !$configIsWritable ? __('Please check the config directory permissions') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=config无权限'
                ]
            ],
            'public_is_writable' => [
                'describe' => self::writableStateDescribe($publicIsWritable),
                'state'    => $publicIsWritable ? self::$ok : self::$fail,
                'need'     => !$publicIsWritable ? __('Please check the public directory permissions') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=public无权限'
                ]
            ],
            'php-mysqli'         => [
                'describe' => $phpMysqli ? __('already installed') : __('Not installed'),
                'state'    => $phpMysqli ? self::$ok : self::$fail,
                'need'     => !$phpMysqli ? __('Mysqli extension for PHP is required') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=安装mysqli扩展'
                ]
            ],
            'php_safe_mode'      => [
                'describe' => $phpSafeMode ? __('open') : __('close'),
                'state'    => $phpSafeMode ? self::$warn : self::$ok,
                'need'     => $phpSafeMode ? __('The installation can continue, and some operations need to be completed manually') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=开启php安全模式'
                ]
            ],
            'php_popen'          => [
                'describe' => $phpPopen ? __('Allow execution') : __('disabled'),
                'state'    => $phpPopen ? self::$ok : self::$warn,
                'need'     => !$phpPopen ? __('The installation can continue, and some operations need to be completed manually') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=解除php popen 函数禁用'
                ]
            ],
            'php_file_operation' => [
                'describe' => $phpFileOperation ? __('Allow operation') : __('disabled'),
                'state'    => $phpFileOperation ? self::$ok : self::$warn,
                'need'     => !$phpFileOperation ? __('The installation can continue, and some operations need to be completed manually') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=解除php fgets 函数禁用'
                ]
            ],
        ]);
    }

    public function envNpmCheck()
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error('', [], 2);
        }

        $npmVersion           = Version::getNpmVersion();
        $npmVersionCompare    = Version::compare('8.3.0', $npmVersion);
        $cnpmVersion          = Version::getCnpmVersion();
        $cnpmVersionCompare   = Version::compare('7.1.0', $cnpmVersion);
        $nodejsVersion        = Version::getNodeJsVersion();
        $nodejsVersionCompare = Version::compare('16.13.0', $nodejsVersion);

        $this->success('ok', [
            'npm_version'    => [
                'describe' => $npmVersion ? $npmVersion : __('Acquisition failed'),
                'state'    => $npmVersionCompare ? self::$ok : self::$warn,
                'need'     => !$npmVersionCompare ? __('The installation can continue, and some operations need to be completed manually') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=npm版本不足'
                ]
            ],
            'cnpm_version'   => [
                'describe' => $cnpmVersion ? $cnpmVersion : __('Acquisition failed'),
                'state'    => $cnpmVersionCompare ? self::$ok : self::$warn,
                'need'     => (!$cnpmVersion && $npmVersionCompare) ? __('It is recommended to add cnpm. Click Install') : ($cnpmVersion ? '' : __('The installation can continue, and some operations need to be completed manually')),
                'click'    => [
                    'title' => (!$cnpmVersion && $npmVersionCompare) ? __('Click Install cnpm') : __('Click to see how to solve it'),
                    'type'  => (!$cnpmVersion && $npmVersionCompare) ? 'install-cnpm' : 'faq',
                    'url'   => 'https://baidu.com?wd=cnpm版本不足'
                ]
            ],
            'nodejs_version' => [
                'describe' => $nodejsVersion ? $nodejsVersion : __('Acquisition failed'),
                'state'    => $nodejsVersionCompare ? self::$ok : self::$warn,
                'need'     => !$nodejsVersionCompare ? __('The installation can continue, and some operations need to be completed manually') : '',
                'click'    => [
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://baidu.com?wd=nodejs版本不足'
                ]
            ]
        ]);
    }

    public function testDatabase()
    {
        error_reporting(0);
        mysqli_report(MYSQLI_REPORT_OFF);

        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 6);

        $database = [
            'hostname' => $this->request->post('hostname'),
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'hostport' => $this->request->post('hostport'),
        ];

        $conn->real_connect($database['hostname'] . ':' . $database['hostport'], $database['username'], $database['password']);
        if ($conn->connect_error) {
            $this->error('数据库链接失败:' . mb_convert_encoding($conn->connect_error, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
        } else {
            $databases        = [];
            $databasesExclude = ['information_schema', 'mysql', 'performance_schema', 'sys'];
            $res              = $conn->query("SHOW DATABASES");
            while ($row = mysqli_fetch_assoc($res)) {
                if (!in_array($row['Database'], $databasesExclude)) {
                    $databases[] = $row['Database'];
                }
            }
            $conn->close();
            $this->success('ok', [
                'databases' => $databases
            ]);
        }
    }

    public function baseConfig()
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
        }

        $param = $this->request->only(['hostname', 'username', 'password', 'hostport', 'database', 'prefix', 'adminname', 'adminpassword', 'sitename']);

        // 写入数据库配置文件
        $dbConfigFile    = config_path() . self::$dbConfigFileName;
        $dbConfigContent = @file_get_contents($dbConfigFile);
        $callback        = function ($matches) use ($param) {
            $value = $param[$matches[1]] ?? '';
            return "'{$matches[1]}'{$matches[2]}=>{$matches[3]}env('database.{$matches[1]}', '{$value}'),";
        };
        $dbConfigText    = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)env\('database\.(.*)',\s+'(.*)'\)\,/", $callback, $dbConfigContent);
        $result          = @file_put_contents($dbConfigFile, $dbConfigText);
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['config/' . self::$dbConfigFileName]));
        }

        // 建立安装锁文件
        $result = @file_put_contents(public_path() . self::$lockFileName, date('Y-m-d H:i:s'));
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['public/' . self::$lockFileName]));
        }

        $this->success('ok');
    }

    public function mvDist()
    {
        $distPath      = root_path() . self::$distDir . DIRECTORY_SEPARATOR;
        $indexHtmlPath = $distPath . 'index.html';
        $assetsPath    = $distPath . 'assets';

        if (!file_exists($indexHtmlPath) || !file_exists($assetsPath)) {
            $this->error('没有找到构建好的前端文件，请手动重新构建！');
        }

        $toIndexHtmlPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'index.html';
        $toAssetsPath    = root_path() . 'public' . DIRECTORY_SEPARATOR . 'assets';
        @unlink($toIndexHtmlPath);
        deldir($toAssetsPath);

        if (rename($indexHtmlPath, $toIndexHtmlPath) && rename($assetsPath, $toAssetsPath)) {
            deldir($distPath);
            $this->success('ok');
        } else {
            $this->error('移动构建好的前端文件失败，请手动移动！');
        }
    }

    private static function writableStateDescribe($writable): string
    {
        return $writable ? __('Writable') : __('No write permission');
    }
}
