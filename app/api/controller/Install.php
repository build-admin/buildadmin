<?php
declare (strict_types=1);

namespace app\api\controller;

use ba\Version;
use app\common\controller\Api;
use think\App;
use ba\CommandExec;

/**
 *
 */
class Install extends Api
{
    /**
     * 环境检查状态
     */
    static $ok   = 'ok';
    static $fail = 'fail';
    static $warn = 'warn';

    /**
     * 安装锁文件名称
     */
    static $lockFileName = 'install.lock';

    /**
     * 数据库配置文件
     */
    static $dbConfigFileName = 'database.php';

    /**
     * 自动构建的前端文件的 outDir 相对于根目录
     */
    static $distDir = 'dist';

    /**
     * 需要的PHP版本
     */
    static $needPHPVersion = '7.1.0';

    /**
     * 需要的Npm版本
     */
    static $needNpmVersion = '7.0.0';

    /**
     * 需要的Cnpm版本
     */
    static $needCnpmVersion = '7.1.0';

    /**
     * 需要的NodeJs版本
     */
    static $needNodejsVersion = '14.13.1';

    /**
     * 安装完成标记
     * 配置完成则建立lock文件
     * 编辑命令成功执行再写入标记到lock文件
     * 实现命令执行失败，刷新页面重新执行
     */
    static $InstallationCompletionMark = 'install-end';


    /**
     * 构造方法
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        set_time_limit(120);
    }

    /**
     * 命令执行窗口
     */
    public function popenWindow()
    {
        // 安装锁
        if (is_file(public_path() . self::$lockFileName)) {
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents == self::$InstallationCompletionMark) {
                return;
            }
        }

        CommandExec::instance()->popenWindow();
    }

    /**
     * 环境基础检查
     */
    public function envBaseCheck()
    {
        // 安装锁
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]), [], 3);
        }

        // php版本-start
        $phpVersion        = phpversion();
        $phpVersionCompare = Version::compare(self::$needPHPVersion, $phpVersion);
        if (!$phpVersionCompare) {
            $phpVersionLink = [
                [
                    // 需要PHP版本
                    'name' => __('need') . ' >= ' . self::$needPHPVersion,
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653891'
                ]
            ];
        }
        // php版本-end

        // 数据库配置文件-start
        $dbConfigFile     = config_path() . self::$dbConfigFileName;
        $configIsWritable = path_is_writable(config_path()) && path_is_writable($dbConfigFile);
        if (!$configIsWritable) {
            $configIsWritableLink = [
                [
                    // 查看原因
                    'name'  => __('View reason'),
                    'title' => __('Click to view the reason'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653892'
                ]
            ];
        }
        // 数据库配置文件-end

        // public-start
        $publicIsWritable = path_is_writable(public_path());
        if (!$publicIsWritable) {
            $publicIsWritableLink = [
                [
                    'name'  => __('View reason'),
                    'title' => __('Click to view the reason'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653893'
                ]
            ];
        }
        // public-end

        // Mysqli-start
        $phpMysqli = extension_loaded('mysqli') && extension_loaded("PDO");
        if (!$phpMysqli) {
            $phpMysqliLink = [
                [
                    'name' => __('Mysqli and PDO extensions need to be installed'),
                    'type' => 'text'
                ],
                [
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653894'
                ]
            ];
        }
        // Mysqli-end

        // popen-start
        $phpPopen = function_exists('popen') && function_exists('pclose');
        if (!$phpPopen) {
            $phpPopenLink = [
                [
                    'name'  => __('View reason'),
                    'title' => __('Popen and Pclose functions in PHP Ini is disabled'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653895'
                ],
                [
                    'name'  => __('How to modify'),
                    'title' => __('Click to view how to modify'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653895'
                ],
                [
                    'name'  => __('Security assurance?'),
                    'title' => __('Using the installation service correctly will not cause any potential security problems. Click to view the details'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653889'
                ],
            ];
        }
        // popen-end

        // 文件操作-start
        $phpFileOperation = function_exists('feof') && function_exists('fgets');
        if (!$phpFileOperation) {
            $phpFileOperationLink = [
                [
                    'name'  => __('View reason'),
                    'title' => __('Feof and fgets functions in PHP Ini is disabled'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653896'
                ],
                [
                    'name'  => __('How to modify'),
                    'title' => __('Click to view how to modify'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653896'
                ],
                [
                    'name'  => __('Security assurance?'),
                    'title' => __('Using the installation service correctly will not cause any potential security problems. Click to view the details'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653889'
                ],
            ];
        }
        // 文件操作-end

        $this->success('', [
            'php_version'        => [
                'describe' => $phpVersion,
                'state'    => $phpVersionCompare ? self::$ok : self::$fail,
                'link'     => $phpVersionLink ?? [],
            ],
            'config_is_writable' => [
                'describe' => self::writableStateDescribe($configIsWritable),
                'state'    => $configIsWritable ? self::$ok : self::$fail,
                'link'     => $configIsWritableLink ?? []
            ],
            'public_is_writable' => [
                'describe' => self::writableStateDescribe($publicIsWritable),
                'state'    => $publicIsWritable ? self::$ok : self::$fail,
                'link'     => $publicIsWritableLink ?? []
            ],
            'php-mysqli'         => [
                'describe' => $phpMysqli ? __('already installed') : __('Not installed'),
                'state'    => $phpMysqli ? self::$ok : self::$fail,
                'link'     => $phpMysqliLink ?? []
            ],
            'php_popen'          => [
                'describe' => $phpPopen ? __('Allow execution') : __('disabled'),
                'state'    => $phpPopen ? self::$ok : self::$warn,
                'link'     => $phpPopenLink ?? []
            ],
            'php_file_operation' => [
                'describe' => $phpFileOperation ? __('Allow operation') : __('disabled'),
                'state'    => $phpFileOperation ? self::$ok : self::$warn,
                'link'     => $phpFileOperationLink ?? []
            ],
        ]);
    }

    /**
     * npm环境检查
     */
    public function envNpmCheck()
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error('', [], 2);
        }

        $npmVersion        = Version::getNpmVersion();
        $npmVersionCompare = Version::compare(self::$needNpmVersion, $npmVersion);
        if (!$npmVersionCompare || !$npmVersion) {
            $npmVersionLink = [
                [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needNpmVersion,
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653897'
                ]
            ];
        }

        $cnpmVersion        = Version::getCnpmVersion();
        $cnpmVersionCompare = Version::compare(self::$needCnpmVersion, $cnpmVersion);
        if (!$cnpmVersionCompare || !$cnpmVersion) {
            $cnpmVersionLink[] = [
                // 需要版本
                'name' => __('need') . ' >= ' . self::$needCnpmVersion,
                'type' => 'text'
            ];

            if ($npmVersionCompare) {
                $cnpmVersionLink[] = [
                    // 点击安装
                    'name'  => __('Click Install cnpm'),
                    'title' => '',
                    'type'  => 'install-cnpm'
                ];
            } else {
                $cnpmVersionLink[] = [
                    // 请先安装npm
                    'name' => __('Please install NPM first'),
                    'type' => 'text'
                ];
            }
        } elseif (!$cnpmVersionCompare && $cnpmVersion) {
            $cnpmVersionLink[] = [
                // 需要版本
                'name' => __('need') . ' >= ' . self::$needCnpmVersion,
                'type' => 'text'
            ];
            $cnpmVersionLink[] = [
                // 如何解决
                'name'  => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type'  => 'faq',
                'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653898'
            ];
        }

        $nodejsVersion        = Version::getNodeJsVersion();
        $nodejsVersionCompare = Version::compare(self::$needNodejsVersion, $nodejsVersion);
        if (!$nodejsVersionCompare || !$nodejsVersion) {
            $nodejsVersionLink = [
                [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needNodejsVersion,
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://www.kancloud.cn/buildadmin/buildadmin/2653899'
                ]
            ];
        }

        $this->success('', [
            'npm_version'    => [
                'describe' => $npmVersion ? $npmVersion : __('Acquisition failed'),
                'state'    => $npmVersionCompare ? self::$ok : self::$warn,
                'link'     => $npmVersionLink ?? [],
            ],
            'nodejs_version' => [
                'describe' => $nodejsVersion ? $nodejsVersion : __('Acquisition failed'),
                'state'    => $nodejsVersionCompare ? self::$ok : self::$warn,
                'link'     => $nodejsVersionLink ?? []
            ],
            'cnpm_version'   => [
                'describe' => $cnpmVersion ? $cnpmVersion : __('Acquisition failed'),
                'state'    => $cnpmVersionCompare ? self::$ok : self::$warn,
                'link'     => $cnpmVersionLink ?? []
            ]
        ]);
    }

    /**
     * 数据库连接-获取数据表列表
     * @param $database
     * @return array
     */
    private function testConnectDatabase($database)
    {
        error_reporting(0);
        mysqli_report(MYSQLI_REPORT_OFF);

        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 6);

        $conn->real_connect($database['hostname'] . ':' . $database['hostport'], $database['username'], $database['password']);
        if ($conn->connect_error) {
            return [
                'code' => 0,
                'msg'  => __('Database connection failed:%s', [mb_convert_encoding($conn->connect_error, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')])
            ];
        } else {
            $databases = [];
            // 不需要的数据表
            $databasesExclude = ['information_schema', 'mysql', 'performance_schema', 'sys'];
            $res              = $conn->query("SHOW DATABASES");
            while ($row = mysqli_fetch_assoc($res)) {
                if (!in_array($row['Database'], $databasesExclude)) {
                    $databases[] = $row['Database'];
                }
            }
            $conn->close();

            return [
                'code'      => 1,
                'msg'       => '',
                'databases' => $databases,
            ];
        }
    }

    /**
     * 测试数据库连接
     */
    public function testDatabase()
    {
        $database = [
            'hostname' => $this->request->post('hostname'),
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'hostport' => $this->request->post('hostport'),
        ];

        $conn = $this->testConnectDatabase($database);
        if ($conn['code'] == 0) {
            $this->error($conn['msg']);
        } else {
            $this->success('', [
                'databases' => $conn['databases']
            ]);
        }
    }

    /**
     * 系统基础配置
     * post请求=开始安装
     */
    public function baseConfig()
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
        }

        $envOk = $this->commandExecutionCheck();
        if ($this->request->isGet()) {
            $this->success('', ['envOk' => $envOk]);
        }

        $param = $this->request->only(['hostname', 'username', 'password', 'hostport', 'database', 'prefix', 'adminname', 'adminpassword', 'sitename']);

        // 检测数据库连接
        $conn = $this->testConnectDatabase($param);
        if ($conn['code'] == 0) {
            $this->error($conn['msg']);
        }

        // 检测数据库是否存在
        if (!in_array($param['database'], $conn['databases'])) {
            $this->error(__('Database does not exist'));
        }

        // 导入安装sql

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
            $this->error(__('File has no write permission:%s', ['config/' . self::$dbConfigFileName]), [], 101);
        }

        // 建立安装锁文件
        $result = @file_put_contents(public_path() . self::$lockFileName, date('Y-m-d H:i:s'));
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['public/' . self::$lockFileName]), [], 102);
        }

        $this->success('', [
            'execution' => $envOk
        ]);
    }

    /**
     * 移动编译好的文件到public
     */
    public function mvDist()
    {
        $distPath      = root_path() . self::$distDir . DIRECTORY_SEPARATOR;
        $indexHtmlPath = $distPath . 'index.html';
        $assetsPath    = $distPath . 'assets';

        if (!file_exists($indexHtmlPath) || !file_exists($assetsPath)) {
            $this->error(__('No built front-end file found, please rebuild manually!'), [], 103);
        }

        $toIndexHtmlPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'index.html';
        $toAssetsPath    = root_path() . 'public' . DIRECTORY_SEPARATOR . 'assets';
        @unlink($toIndexHtmlPath);
        deldir($toAssetsPath);

        if (rename($indexHtmlPath, $toIndexHtmlPath) && rename($assetsPath, $toAssetsPath)) {
            deldir($distPath);
            $result = @file_put_contents(public_path() . self::$lockFileName, self::$InstallationCompletionMark);
            if (!$result) {
                $this->error(__('File has no write permission:%s', ['public/' . self::$lockFileName]), [], 102);
            }
            $this->success('');
        } else {
            $this->error(__('Failed to move the front-end file, please move it manually!'), [], 104);
        }
    }

    /**
     * 获取命令执行检查的结果
     * @return bool 是否拥有执行命令的条件
     */
    private function commandExecutionCheck()
    {
        $check['phpPopen']             = function_exists('popen') && function_exists('pclose');
        $check['phpFileOperation']     = function_exists('feof') && function_exists('fgets');
        $check['npmVersionCompare']    = Version::compare(self::$needNpmVersion, Version::getNpmVersion());
        $check['cnpmVersionCompare']   = Version::compare(self::$needCnpmVersion, Version::getCnpmVersion());
        $check['nodejsVersionCompare'] = Version::compare(self::$needNodejsVersion, Version::getNodeJsVersion());

        $envOk = true;
        foreach ($check as $value) {
            if (!$value) {
                $envOk = false;
                break;
            }
        }
        return $envOk;
    }

    /**
     * 取得 web 目录完整路径
     */
    public function manualInstall()
    {
        $this->success('', [
            'webPath' => root_path() . 'web'
        ]);
    }

    /**
     * 目录是否可写
     * @param $writable
     * @return string
     */
    private static function writableStateDescribe($writable): string
    {
        return $writable ? __('Writable') : __('No write permission');
    }
}
