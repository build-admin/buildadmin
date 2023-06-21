<?php
declare (strict_types=1);

namespace app\api\controller;

use ba\Random;
use ba\Version;
use app\common\controller\Api;
use think\App;
use ba\Terminal;
use think\Exception;
use think\facade\Config;
use think\facade\Db;
use think\db\exception\PDOException;
use app\admin\model\Admin as AdminModel;
use app\admin\model\User as UserModel;

/**
 * 安装控制器
 */
class Install extends Api
{
    protected $useSystemSettings = false;

    /**
     * 环境检查状态
     */
    static string $ok   = 'ok';
    static string $fail = 'fail';
    static string $warn = 'warn';

    /**
     * 安装锁文件名称
     */
    static string $lockFileName = 'install.lock';

    /**
     * 配置文件
     */
    static string $dbConfigFileName    = 'database.php';
    static string $buildConfigFileName = 'buildadmin.php';

    /**
     * 自动构建的前端文件的 outDir 相对于根目录
     */
    static string $distDir = 'web' . DIRECTORY_SEPARATOR . 'dist';

    /**
     * 需要的依赖版本
     */
    static array $needDependentVersion = [
        'php'  => '7.1.0',
        'npm'  => '6.14.0',
        'cnpm' => '7.1.0',
        'node' => '14.13.1',
        'yarn' => '1.2.0',
        'pnpm' => '6.32.13',
    ];

    /**
     * 安装完成标记
     * 配置完成则建立lock文件
     * 执行命令成功执行再写入标记到lock文件
     * 实现命令执行失败，重载页面可重新执行
     */
    static string $InstallationCompletionMark = 'install-end';


    /**
     * 构造方法
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 命令执行窗口
     */
    public function terminal()
    {
        // 安装锁
        if (is_file(public_path() . self::$lockFileName)) {
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents == self::$InstallationCompletionMark) {
                return;
            }
        }

        Terminal::instance()->exec(false);
    }

    public function changePackageManager()
    {
        // 安装锁
        if (is_file(public_path() . self::$lockFileName)) {
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents == self::$InstallationCompletionMark) {
                return;
            }
        }

        $newPackageManager = request()->post('manager', Config::get('terminal.npm_package_manager'));
        if (Terminal::changeTerminalConfig()) {
            $this->success('', [
                'manager' => $newPackageManager
            ]);
        } else {
            $this->error(__('Failed to switch package manager. Please modify the configuration file manually:%s', ['根目录/config/buildadmin.php']));
        }
    }

    /**
     * 环境基础检查
     */
    public function envBaseCheck()
    {
        // 安装锁
        if (is_file(public_path() . self::$lockFileName)) {
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]), []);
        }

        // php版本-start
        $phpVersion        = phpversion();
        $phpVersionCompare = Version::compare(self::$needDependentVersion['php'], $phpVersion);
        if (!$phpVersionCompare) {
            $phpVersionLink = [
                [
                    // 需要PHP版本
                    'name' => __('need') . ' >= ' . self::$needDependentVersion['php'],
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/preparePHP.html'
                ]
            ];
        }
        // php版本-end

        // 配置文件-start
        $dbConfigFile     = config_path() . self::$dbConfigFileName;
        $configIsWritable = path_is_writable(config_path()) && path_is_writable($dbConfigFile);
        if (!$configIsWritable) {
            $configIsWritableLink = [
                [
                    // 查看原因
                    'name'  => __('View reason'),
                    'title' => __('Click to view the reason'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/dirNoPermission.html'
                ]
            ];
        }
        // 配置文件-end

        // public-start
        $publicIsWritable = path_is_writable(public_path());
        if (!$publicIsWritable) {
            $publicIsWritableLink = [
                [
                    'name'  => __('View reason'),
                    'title' => __('Click to view the reason'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/dirNoPermission.html'
                ]
            ];
        }
        // public-end

        // PDO-start
        $phpPdo = extension_loaded("PDO");
        if (!$phpPdo) {
            $phpPdoLink = [
                [
                    'name' => __('PDO extensions need to be installed'),
                    'type' => 'text'
                ],
                [
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/missingExtension.html'
                ]
            ];
        }
        // PDO-end

        // proc_open
        $phpProc = function_exists('proc_open') && function_exists('proc_close') && function_exists('proc_get_status');
        if (!$phpProc) {
            $phpProcLink = [
                [
                    'name'  => __('View reason'),
                    'title' => __('proc_open or proc_close functions in PHP Ini is disabled'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/disablement.html'
                ],
                [
                    'name'  => __('How to modify'),
                    'title' => __('Click to view how to modify'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/disablement.html'
                ],
                [
                    'name'  => __('Security assurance?'),
                    'title' => __('Using the installation service correctly will not cause any potential security problems. Click to view the details'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/senior.html'
                ],
            ];
        }
        // proc_open-end

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
            'php_pdo'            => [
                'describe' => $phpPdo ? __('already installed') : __('Not installed'),
                'state'    => $phpPdo ? self::$ok : self::$fail,
                'link'     => $phpPdoLink ?? []
            ],
            'php_proc'           => [
                'describe' => $phpProc ? __('Allow execution') : __('disabled'),
                'state'    => $phpProc ? self::$ok : self::$warn,
                'link'     => $phpProcLink ?? []
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

        $packageManager = request()->post('manager', 'none');

        // npm
        $npmVersion        = Version::getVersion('npm');
        $npmVersionCompare = Version::compare(self::$needDependentVersion['npm'], $npmVersion);
        if (!$npmVersionCompare || !$npmVersion) {
            $npmVersionLink = [
                [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needDependentVersion['npm'],
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/prepareNpm.html'
                ]
            ];
        }

        // 包管理器
        if (in_array($packageManager, ['npm', 'cnpm', 'pnpm', 'yarn'])) {
            $pmVersion        = Version::getVersion($packageManager);
            $pmVersionCompare = Version::compare(self::$needDependentVersion[$packageManager], $pmVersion);

            if (!$pmVersion) {
                // 安装
                $pmVersionLink[] = [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needDependentVersion[$packageManager],
                    'type' => 'text'
                ];
                if ($npmVersionCompare) {
                    $pmVersionLink[] = [
                        // 点击安装
                        'name'  => __('Click Install %s', [$packageManager]),
                        'title' => '',
                        'type'  => 'install-package-manager'
                    ];
                } else {
                    $pmVersionLink[] = [
                        // 请先安装npm
                        'name' => __('Please install NPM first'),
                        'type' => 'text'
                    ];
                }
            } elseif (!$pmVersionCompare) {
                // 版本不足
                $pmVersionLink[] = [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needDependentVersion[$packageManager],
                    'type' => 'text'
                ];
                $pmVersionLink[] = [
                    // 请升级
                    'name' => __('Please upgrade %s version', [$packageManager]),
                    'type' => 'text'
                ];
            }
        } elseif ($packageManager == 'ni') {
            $pmVersion        = __('nothing');
            $pmVersionCompare = true;
        } else {
            $pmVersion        = __('nothing');
            $pmVersionCompare = false;
        }

        // nodejs
        $nodejsVersion        = Version::getVersion('node');
        $nodejsVersionCompare = Version::compare(self::$needDependentVersion['node'], $nodejsVersion);
        if (!$nodejsVersionCompare || !$nodejsVersion) {
            $nodejsVersionLink = [
                [
                    // 需要版本
                    'name' => __('need') . ' >= ' . self::$needDependentVersion['node'],
                    'type' => 'text'
                ],
                [
                    // 如何解决
                    'name'  => __('How to solve?'),
                    'title' => __('Click to see how to solve it'),
                    'type'  => 'faq',
                    'url'   => 'https://wonderful-code.gitee.io/guide/install/prepareNodeJs.html'
                ]
            ];
        }

        $this->success('', [
            'npm_version'         => [
                'describe' => $npmVersion ?: __('Acquisition failed'),
                'state'    => $npmVersionCompare ? self::$ok : self::$warn,
                'link'     => $npmVersionLink ?? [],
            ],
            'nodejs_version'      => [
                'describe' => $nodejsVersion ?: __('Acquisition failed'),
                'state'    => $nodejsVersionCompare ? self::$ok : self::$warn,
                'link'     => $nodejsVersionLink ?? []
            ],
            'npm_package_manager' => [
                'describe' => $pmVersion ?: __('Acquisition failed'),
                'state'    => $pmVersionCompare ? self::$ok : self::$warn,
                'link'     => $pmVersionLink ?? [],
            ]
        ]);
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
            'database' => '',
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
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents != self::$InstallationCompletionMark) {
                $this->error('Retry Build', [], 302);
            }
            $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
        }

        $envOk = $this->commandExecutionCheck();
        if ($this->request->isGet()) {
            $this->success('', ['envOk' => $envOk]);
        }

        $param = $this->request->only(['hostname', 'username', 'password', 'hostport', 'database', 'prefix', 'adminname', 'adminpassword', 'sitename']);

        // 数据库配置测试
        try {
            $dbConfig                                     = Config::get('database');
            $dbConfig['connections']['mysql']['hostname'] = $param['hostname'];
            $dbConfig['connections']['mysql']['database'] = $param['database'];
            $dbConfig['connections']['mysql']['username'] = $param['username'];
            $dbConfig['connections']['mysql']['password'] = $param['password'];
            $dbConfig['connections']['mysql']['hostport'] = $param['hostport'];
            $dbConfig['connections']['mysql']['prefix']   = $param['prefix'];
            Config::set(['connections' => $dbConfig['connections']], 'database');

            $connect = Db::connect('mysql');
            $connect->execute("SELECT 1");
        } catch (PDOException $e) {
            $this->error(__('Database connection failed:%s', [mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')]));
        }

        // 导入安装sql
        try {
            $sql = file_get_contents(root_path() . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'buildadmin.sql');
            $sql = str_replace("__PREFIX__", $param['prefix'], $sql);
            $connect->getPdo()->exec($sql);
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            $this->error(__('Failed to install SQL execution:%s', [mb_convert_encoding($errorMsg ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')]));
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $this->error(__('Installation error:%s', [mb_convert_encoding($errorMsg ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')]));
        }

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

        // 写入.env-example文件
        $envFile        = root_path() . '.env-example';
        $envFileContent = @file_get_contents($envFile);
        if ($envFileContent && stripos($envFileContent, '[DATABASE]') === false) {
            $envFileContent .= "\n" . '[DATABASE]' . "\n";
            $envFileContent .= 'TYPE = mysql' . "\n";
            $envFileContent .= 'HOSTNAME = ' . $param['hostname'] . "\n";
            $envFileContent .= 'DATABASE = ' . $param['database'] . "\n";
            $envFileContent .= 'USERNAME = ' . $param['username'] . "\n";
            $envFileContent .= 'PASSWORD = ' . $param['password'] . "\n";
            $envFileContent .= 'HOSTPORT = ' . $param['hostport'] . "\n";
            $envFileContent .= 'CHARSET = utf8' . "\n";
            $envFileContent .= 'DEBUG = true' . "\n";
            $result         = @file_put_contents($envFile, $envFileContent);
            if (!$result) {
                $this->error(__('File has no write permission:%s', ['/' . $envFile]));
            }
        }

        // 设置新的Token随机密钥key
        $oldTokenKey        = Config::get('buildadmin.token.key');
        $newTokenKey        = Random::build('alnum', 32);
        $buildConfigFile    = config_path() . self::$buildConfigFileName;
        $buildConfigContent = @file_get_contents($buildConfigFile);
        $buildConfigContent = preg_replace("/'key'(\s+)=>(\s+)'{$oldTokenKey}'/", "'key'\$1=>\$2'{$newTokenKey}'", $buildConfigContent);
        $result             = @file_put_contents($buildConfigFile, $buildConfigContent);
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['config/' . self::$buildConfigFileName]));
        }

        // 管理员配置入库
        $adminModel             = new AdminModel();
        $defaultAdmin           = $adminModel->where('username', 'admin')->find();
        $defaultAdmin->username = $param['adminname'];
        $defaultAdmin->nickname = ucfirst($param['adminname']);
        $defaultAdmin->save();

        if (isset($param['adminpassword']) && $param['adminpassword']) {
            $adminModel->resetPassword($defaultAdmin->id, $param['adminpassword']);
        }

        // 默认用户密码修改
        $user = new UserModel();
        $user->resetPassword(1, Random::build());

        // 修改站点名称
        $connect->table($param['prefix'] . 'config')->where('name', 'site_name')->update([
            'value' => $param['sitename']
        ]);

        // 建立安装锁文件
        $result = @file_put_contents(public_path() . self::$lockFileName, date('Y-m-d H:i:s'));
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['public/' . self::$lockFileName]));
        }

        $this->success('', [
            'execution' => $envOk
        ]);
    }

    /**
     * 标记命令执行完毕
     */
    public function commandExecComplete()
    {
        if (is_file(public_path() . self::$lockFileName)) {
            $contents = @file_get_contents(public_path() . self::$lockFileName);
            if ($contents == self::$InstallationCompletionMark) {
                $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::$lockFileName]));
            }
        }

        $result = @file_put_contents(public_path() . self::$lockFileName, self::$InstallationCompletionMark);
        if (!$result) {
            $this->error(__('File has no write permission:%s', ['public/' . self::$lockFileName]));
        }
        $this->success();
    }

    /**
     * 获取命令执行检查的结果
     * @return bool 是否拥有执行命令的条件
     */
    private function commandExecutionCheck(): bool
    {
        $pm = Config::get('terminal.npm_package_manager');
        if ($pm == 'none') {
            return false;
        }
        $check['phpPopen']             = function_exists('proc_open') && function_exists('proc_close');
        $check['npmVersionCompare']    = Version::compare(self::$needDependentVersion['npm'], Version::getVersion('npm'));
        $check['pmVersionCompare']     = Version::compare(self::$needDependentVersion[$pm], Version::getVersion($pm));
        $check['nodejsVersionCompare'] = Version::compare(self::$needDependentVersion['node'], Version::getVersion('node'));

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
     * 安装指引
     */
    public function manualInstall()
    {
        $this->success('', [
            'webPath' => str_replace('\\', '/', root_path() . 'web')
        ]);
    }

    public function mvDist()
    {
        if (!is_file(root_path() . self::$distDir . DIRECTORY_SEPARATOR . 'index.html')) {
            $this->error(__('No built front-end file found, please rebuild manually!'));
        }

        if (Terminal::mvDist()) {
            $this->success();
        } else {
            $this->error(__('Failed to move the front-end file, please move it manually!'));
        }
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

    /**
     * 数据库连接-获取数据表列表
     * @param $database
     * @return array
     */
    private function testConnectDatabase($database): array
    {
        try {
            $dbConfig                         = Config::get('database');
            $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], $database);
            Config::set(['connections' => $dbConfig['connections']], 'database');

            $connect = Db::connect('mysql');
            $connect->execute("SELECT 1");
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            return [
                'code' => 0,
                'msg'  => __('Database connection failed:%s', [mb_convert_encoding($errorMsg ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')])
            ];
        }

        $databases = [];
        // 不需要的数据表
        $databasesExclude = ['information_schema', 'mysql', 'performance_schema', 'sys'];
        $res              = $connect->query("SHOW DATABASES");
        foreach ($res as $row) {
            if (!in_array($row['Database'], $databasesExclude)) {
                $databases[] = $row['Database'];
            }
        }

        return [
            'code'      => 1,
            'msg'       => '',
            'databases' => $databases,
        ];
    }
}
