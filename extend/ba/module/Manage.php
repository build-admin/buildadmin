<?php

namespace ba\module;

use ba\Depend;
use think\Exception;
use think\facade\Config;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 模块管理类
 */
class Manage
{
    public const UNINSTALLED            = 0;
    public const INSTALLED              = 1;
    public const WAIT_INSTALL           = 2;
    public const CONFLICT_PENDING       = 3;
    public const DEPENDENT_WAIT_INSTALL = 4;
    public const DIRECTORY_OCCUPIED     = 5;

    /**
     * @var Manage 对象实例
     */
    protected static $instance;

    /**
     * @var string 安装目录
     */
    protected $installDir = null;

    /**
     * @var string 备份目录
     */
    protected $ebakDir = null;

    /**
     * @var string 模板唯一标识
     */
    protected $uid = null;

    /**
     * @var string 模板根目录
     */
    protected $modulesDir = null;

    /**
     * 初始化
     * @access public
     * @param string $uid
     * @return Manage
     */
    public static function instance(string $uid): Manage
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($uid);
        }
        return self::$instance;
    }

    public function __construct(string $uid)
    {
        $this->uid        = $uid;
        $this->installDir = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        $this->ebakDir    = $this->installDir . 'ebak' . DIRECTORY_SEPARATOR;
        $this->modulesDir = $this->installDir . $uid . DIRECTORY_SEPARATOR;
        if (!is_dir($this->installDir)) {
            mkdir($this->installDir, 0755, true);
        }
        if (!is_dir($this->ebakDir)) {
            mkdir($this->ebakDir, 0755, true);
        }
    }

    public function installState()
    {
        if (!is_dir($this->modulesDir)) {
            return self::UNINSTALLED;
        }
        $info = $this->getInfo();
        if ($info && isset($info['state'])) {
            return $info['state'];
        }

        // 目录已存在，但非正常的模块
        return dir_is_empty($this->modulesDir) ? self::UNINSTALLED : self::DIRECTORY_OCCUPIED;
    }

    /**
     * 安装模板或案例
     * @param string $token   用户token
     * @param int    $orderId 订单号
     * @throws moduleException
     * @throws Exception
     */
    public function install(string $token, int $orderId)
    {
        $state = $this->installState();
        if ($state == self::INSTALLED || $state == self::DIRECTORY_OCCUPIED) {
            throw new Exception('Module already exists');
        }

        if ($state == self::UNINSTALLED) {
            if (!$orderId) {
                throw new Exception('Order not found');
            }
            // 下载
            $sysVersion = Config::get('buildadmin.version');
            $zipFile    = Server::download($this->uid, $this->installDir, [
                'sysVersion'    => $sysVersion,
                'ba-user-token' => $token,
                'order_id'      => $orderId,
            ]);

            // 解压
            Server::unzip($zipFile);

            // 删除下载的zip
            @unlink($zipFile);
        }

        // 检查是否完整
        $this->checkPackage();

        // 设置为安装中状态
        $this->setInfo([
            'state' => self::WAIT_INSTALL,
        ]);

        // 导入sql
        Server::importSql($this->modulesDir);

        // 启用插件
        $this->enable();
    }

    public function enable()
    {
        $this->conflictHandle();

        // 执行启用脚本
        Server::execEvent($this->uid, 'enable');

        $info = $this->getInfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            $waitInstall = [];
            if (isset($info['composer_dependent_wait_install'])) {
                $waitInstall[] = 'composer_dependent_wait_install';
            }
            if (isset($info['npm_dependent_wait_install'])) {
                $waitInstall[] = 'npm_dependent_wait_install';
            }
            if ($waitInstall) {
                throw new moduleException('dependent wait install', -2, [
                    'uid'          => $this->uid,
                    'state'        => self::DEPENDENT_WAIT_INSTALL,
                    'wait_install' => $waitInstall,
                ]);
            } else {
                $this->setInfo([
                    'state' => self::INSTALLED,
                ]);
            }
        }
    }

    /**
     * 处理依赖和文件冲突，并完成与前端的冲突处理交互
     * @throws moduleException
     */
    public function conflictHandle()
    {
        // 文件冲突
        $fileConflict = Server::getFileList($this->modulesDir, true);
        // 依赖冲突
        $dependConflict = Server::dependConflictCheck($this->modulesDir);
        // 待安装文件
        $installFiles = Server::getFileList($this->modulesDir);

        $coverFiles   = [];// 要覆盖的文件-备份
        $discardFiles = [];// 抛弃的文件-复制时不覆盖
        $dependObj    = new Depend();
        if ($fileConflict || $dependConflict) {
            $extend = request()->post('extend/a', []);
            if (!$extend) {
                // 发现冲突->手动处理->转换为方便前端使用的格式
                $fileConflictTemp = [];
                foreach ($fileConflict as $key => $item) {
                    $fileConflictTemp[$key] = [
                        'newFile'  => $this->uid . DIRECTORY_SEPARATOR . $item,
                        'oldFile'  => $item,
                        'solution' => 'cover',
                    ];
                }
                $dependConflictTemp = [];
                foreach ($dependConflict as $env => $item) {
                    $dev = !(stripos($env, 'dev') === false);
                    foreach ($item as $depend => $v) {
                        $dependConflictTemp[] = [
                            'env'       => $env,
                            'newDepend' => $depend . ' ' . $v,
                            'oldDepend' => $depend . ' ' . (stripos($env, 'require') === false ? $dependObj->hasNpmDependencies($depend, $dev) : $dependObj->hasComposerRequire($depend, $dev)),
                            'depend'    => $depend,
                            'solution'  => 'cover',
                        ];
                    }
                }
                $this->setInfo([
                    'state' => self::CONFLICT_PENDING,
                ]);
                throw new moduleException('Module file conflicts', -1, [
                    'fileConflict'   => $fileConflictTemp,
                    'dependConflict' => $dependConflictTemp,
                    'uid'            => $this->uid,
                    'state'          => self::CONFLICT_PENDING,
                ]);
            }

            // 处理冲突
            if ($fileConflict && isset($extend['fileConflict'])) {
                foreach ($installFiles as $ikey => $installFile) {
                    if (isset($extend['fileConflict'][$installFile])) {
                        if ($extend['fileConflict'][$installFile] == 'discard') {
                            $discardFiles[] = $installFile;
                            unset($installFiles[$ikey]);
                        } else {
                            $coverFiles[] = $installFile;
                        }
                    }
                }
            }
            if ($dependConflict && isset($extend['dependConflict'])) {
                foreach ($dependConflict as $fKey => $fItem) {
                    foreach ($fItem as $cKey => $cItem) {
                        if (isset($extend['dependConflict'][$fKey][$cKey]) && $extend['dependConflict'][$fKey][$cKey] == 'discard') {
                            unset($dependConflict[$fKey][$cKey]);
                        }
                    }
                }
            }
        }

        if ($dependConflict) {
            $info     = $this->getInfo();
            $npm      = false;
            $composer = false;
            foreach ($dependConflict as $key => $item) {
                if (!$item) {
                    continue;
                }
                if ($key == 'require') {
                    $composer = true;
                    $dependObj->addComposerRequire($item, false, true);
                } elseif ($key == 'require-dev') {
                    $composer = true;
                    $dependObj->addComposerRequire($item, true, true);
                } elseif ($key == 'dependencies') {
                    $npm = true;
                    $dependObj->addNpmDependencies($item, false, true);
                } elseif ($key == 'devDependencies') {
                    $npm = true;
                    $dependObj->addNpmDependencies($item, true, true);
                }
            }
            if ($npm) {
                $info['npm_dependent_wait_install'] = 1;
                $info['state']                      = self::DEPENDENT_WAIT_INSTALL;
            }
            if ($composer) {
                $info['composer_dependent_wait_install'] = 1;
                $info['state']                           = self::DEPENDENT_WAIT_INSTALL;
            }
            if ($info['state'] != self::DEPENDENT_WAIT_INSTALL) {
                // 无冲突
                $this->setInfo([
                    'state' => self::INSTALLED,
                ]);
            } else {
                $this->setInfo([], $info);
            }
        } else {
            // 无冲突
            $this->setInfo([
                'state' => self::INSTALLED,
            ]);
        }

        // 备份将被覆盖的文件
        if ($coverFiles) {
            Server::createZip($coverFiles, $this->ebakDir . $this->uid . '-cover-' . date('YmdHis') . '.zip');
        }

        // 复制文件
        $overwriteDir = Server::getOverwriteDir();
        foreach ($overwriteDir as $dirItem) {
            $baseDir = $this->modulesDir . $dirItem;
            $destDir = root_path() . $dirItem;
            if (!is_dir($baseDir)) {
                continue;
            }
            foreach (
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                ) as $item
            ) {
                $destDirItem = $destDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                if ($item->isDir()) {
                    if (!is_dir($destDirItem)) {
                        mkdir($destDirItem, 0755, true);
                    }
                } else {
                    if (!in_array(str_replace(root_path(), '', $destDirItem), $discardFiles)) {
                        copy($item, $destDirItem);
                    }
                }
            }
        }
    }

    public function checkPackage(): bool
    {
        if (!is_dir($this->modulesDir)) {
            throw new Exception('Module package file does not exist');
        }
        $info     = $this->getInfo();
        $infoKeys = ['uid', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($infoKeys as $value) {
            if (!array_key_exists($value, $info)) {
                deldir($this->modulesDir);
                throw new Exception('Basic configuration of the Module is incomplete');
            }
        }
        return true;
    }

    public function getInfo()
    {
        return Server::getIni($this->modulesDir);
    }

    public function setInfo(array $kv = [], array $arr = []): bool
    {
        if ($kv) {
            $info = $this->getInfo();
            foreach ($kv as $k => $v) {
                $info[$k] = $v;
            }
            return Server::setIni($this->modulesDir, $info);
        } elseif ($arr) {
            return Server::setIni($this->modulesDir, $arr);
        }
        throw new Exception('Parameter error');
    }
}