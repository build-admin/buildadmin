<?php

namespace app\admin\library\module;

use Throwable;
use ba\Version;
use ba\Depends;
use ba\Exception;
use ba\Filesystem;
use FilesystemIterator;
use think\facade\Config;
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
    public const DISABLE                = 6;

    /**
     * @var ?Manage 对象实例
     */
    protected static ?Manage $instance = null;

    /**
     * @var string 安装目录
     */
    protected string $installDir;

    /**
     * @var string 备份目录
     */
    protected string $backupsDir;

    /**
     * @var string 模板唯一标识
     */
    protected string $uid;

    /**
     * @var string 模板根目录
     */
    protected string $modulesDir;

    /**
     * 初始化
     * @access public
     * @param string $uid
     * @return Manage
     */
    public static function instance(string $uid = ''): Manage
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($uid);
        }
        return self::$instance;
    }

    public function __construct(string $uid)
    {
        $this->installDir = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        $this->backupsDir = $this->installDir . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($this->installDir)) {
            mkdir($this->installDir, 0755, true);
        }
        if (!is_dir($this->backupsDir)) {
            mkdir($this->backupsDir, 0755, true);
        }

        if ($uid) {
            $this->uid        = $uid;
            $this->modulesDir = $this->installDir . $uid . DIRECTORY_SEPARATOR;
        }
    }

    public function getInstallState()
    {
        if (!is_dir($this->modulesDir)) {
            return self::UNINSTALLED;
        }
        $info = $this->getInfo();
        if ($info && isset($info['state'])) {
            return $info['state'];
        }

        // 目录已存在，但非正常的模块
        return Filesystem::dirIsEmpty($this->modulesDir) ? self::UNINSTALLED : self::DIRECTORY_OCCUPIED;
    }

    /**
     * 下载模块文件
     * @param string $token   官网会员token
     * @param int    $orderId 订单号
     * @return string 下载文件路径
     * @throws Throwable
     */
    public function download(string $token, int $orderId): string
    {
        if (!$orderId) {
            throw new Exception('Order not found');
        }
        // 下载
        $installed = Server::installedList($this->installDir);
        foreach ($installed as $item) {
            $installedIds[] = $item['uid'];
        }
        unset($installed);
        $zipFile = Server::download($this->uid, $this->installDir, [
            'sysVersion'    => Config::get('buildadmin.version'),
            'nuxtVersion'   => Server::getNuxtVersion(),
            'ba-user-token' => $token,
            'order_id'      => $orderId,
            // 传递已安装模块，做互斥检测
            'installed'     => $installedIds ?? [],
        ]);

        // 删除旧版本代码
        Filesystem::delDir($this->modulesDir);

        // 解压
        Filesystem::unzip($zipFile);

        // 删除下载的zip
        @unlink($zipFile);

        // 检查是否完整
        $this->checkPackage();

        // 设置为待安装状态
        $this->setInfo([
            'state' => self::WAIT_INSTALL,
        ]);

        return $zipFile;
    }

    /**
     * 上传安装
     * @param string $file 已经上传完成的文件
     * @return array 模块的基本信息
     * @throws Throwable
     */
    public function upload(string $file): array
    {
        $file = Filesystem::fsFit(root_path() . 'public' . $file);
        if (!is_file($file)) {
            // 包未找到
            throw new Exception('Zip file not found');
        }

        $copyTo = $this->installDir . 'uploadTemp' . date('YmdHis') . '.zip';
        copy($file, $copyTo);

        // 解压
        $copyToDir = Filesystem::unzip($copyTo);
        $copyToDir .= DIRECTORY_SEPARATOR;

        // 删除zip
        @unlink($file);
        @unlink($copyTo);

        // 读取ini
        $info = Server::getIni($copyToDir);
        if (empty($info['uid'])) {
            Filesystem::delDir($copyToDir);
            // 基本配置不完整
            throw new Exception('Basic configuration of the Module is incomplete');
        }
        $this->uid        = $info['uid'];
        $this->modulesDir = $this->installDir . $info['uid'] . DIRECTORY_SEPARATOR;

        $upgrade = false;
        if (is_dir($this->modulesDir)) {
            $oldInfo = $this->getInfo();
            if ($oldInfo && !empty($oldInfo['uid'])) {
                $versions = explode('.', $oldInfo['version']);
                if (isset($versions[2])) {
                    $versions[2]++;
                }
                $nextVersion = implode('.', $versions);
                $upgrade     = Version::compare($nextVersion, $info['version']);
                if (!$upgrade) {
                    Filesystem::delDir($copyToDir);
                    // 模块已经存在
                    throw new Exception('Module already exists');
                }
            }

            if (!Filesystem::dirIsEmpty($this->modulesDir) && !$upgrade) {
                Filesystem::delDir($copyToDir);
                // 模块目录被占
                throw new Exception('The directory required by the module is occupied');
            }
        }

        $newInfo = ['state' => self::WAIT_INSTALL];
        if ($upgrade) {
            $newInfo['update'] = 1;

            // 清理旧版本代码
            Filesystem::delDir($this->modulesDir);
        }

        // 放置新模块
        rename($copyToDir, $this->modulesDir);

        // 检查新包是否完整
        $this->checkPackage();

        // 设置为待安装状态
        $this->setInfo($newInfo);

        return $info;
    }

    /**
     * 更新
     * @throws Throwable
     */
    public function update(string $token, int $orderId): void
    {
        $state = $this->getInstallState();
        if ($state != self::DISABLE) {
            throw new Exception('Please disable the module before updating');
        }

        $this->download($token, $orderId);

        // 标记需要执行更新脚本，并在安装请求执行（当前请求未自动加载到新文件不方便执行）
        $info           = $this->getInfo();
        $info['update'] = 1;
        $this->setInfo([], $info);
    }

    /**
     * 安装模块
     * @param string $token   用户token
     * @param int    $orderId 订单号
     * @return array 模块基本信息
     * @throws Throwable
     */
    public function install(string $token, int $orderId): array
    {
        $state = $this->getInstallState();
        if ($state == self::INSTALLED || $state == self::DIRECTORY_OCCUPIED || $state == self::DISABLE) {
            throw new Exception('Module already exists');
        }

        if ($state == self::UNINSTALLED) {
            $this->download($token, $orderId);
        }

        // 导入sql
        Server::importSql($this->modulesDir);

        // 如果是更新，先执行更新脚本
        $info = $this->getInfo();
        if (isset($info['update']) && $info['update']) {
            Server::execEvent($this->uid, 'update');
            unset($info['update']);
            $this->setInfo([], $info);
        }

        // 执行安装脚本
        Server::execEvent($this->uid, 'install');

        // 启用插件
        $this->enable('install');

        return $info;
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        $info = $this->getInfo();
        if ($info['state'] != self::DISABLE) {
            throw new Exception('Please disable the module first', 0, [
                'uid' => $this->uid,
            ]);
        }

        // 执行卸载脚本
        Server::execEvent($this->uid, 'uninstall');

        Filesystem::delDir($this->modulesDir);
    }

    /**
     * 修改模块状态
     * @param bool $state 新状态
     * @return array 模块基本信息
     * @throws Throwable
     */
    public function changeState(bool $state): array
    {
        $info = $this->getInfo();
        if (!$state) {
            $canDisable = [
                self::INSTALLED,
                self::CONFLICT_PENDING,
                self::DEPENDENT_WAIT_INSTALL,
            ];
            if (!in_array($info['state'], $canDisable)) {
                throw new Exception('The current state of the module cannot be set to disabled', 0, [
                    'uid'   => $this->uid,
                    'state' => $info['state'],
                ]);
            }
            return $this->disable();
        }

        if ($info['state'] != self::DISABLE) {
            throw new Exception('The current state of the module cannot be set to enabled', 0, [
                'uid'   => $this->uid,
                'state' => $info['state'],
            ]);
        }
        $this->setInfo([
            'state' => self::WAIT_INSTALL,
        ]);
        return $info;
    }

    /**
     * 启用
     * @param string $trigger 触发启用的标志，比如:install=安装
     * @throws Throwable
     */
    public function enable(string $trigger): void
    {
        // 安装 WebBootstrap
        Server::installWebBootstrap($this->uid, $this->modulesDir);

        // 建立 .runtime
        Server::createRuntime($this->modulesDir);

        // 冲突检查
        $this->conflictHandle($trigger);

        // 执行启用脚本
        Server::execEvent($this->uid, 'enable');

        $this->dependUpdateHandle();
    }

    /**
     * 禁用
     * @return array 模块基本信息
     * @throws Throwable
     */
    public function disable(): array
    {
        $update                 = request()->post("update/b", false);
        $confirmConflict        = request()->post("confirmConflict/b", false);
        $dependConflictSolution = request()->post("dependConflictSolution/a", []);

        $info    = $this->getInfo();
        $zipFile = $this->backupsDir . $this->uid . '-install.zip';
        $zipDir  = false;
        if (is_file($zipFile)) {
            try {
                $zipDir = $this->backupsDir . $this->uid . '-install' . DIRECTORY_SEPARATOR;
                Filesystem::unzip($zipFile, $zipDir);
            } catch (Exception) {
                // skip
            }
        }

        $conflictFile   = Server::getFileList($this->modulesDir, true);
        $dependConflict = $this->disableDependCheck();
        if (($conflictFile || !self::isEmptyArray($dependConflict)) && !$confirmConflict) {
            $dependConflictTemp = [];
            foreach ($dependConflict as $env => $item) {
                foreach ($item as $depend => $v) {
                    $dependConflictTemp[] = [
                        'env'         => $env,
                        'depend'      => $depend,
                        'dependTitle' => $depend . ' ' . $v,
                        'solution'    => 'delete',
                    ];
                }
            }
            throw new Exception('Module file updated', -1, [
                'uid'            => $this->uid,
                'conflictFile'   => $conflictFile,
                'dependConflict' => $dependConflictTemp,
            ]);
        }

        // 执行禁用脚本
        Server::execEvent($this->uid, 'disable', ['update' => $update]);

        // 是否需要备份依赖？
        $delNpmDepend      = false;
        $delNuxtNpmDepend  = false;
        $delComposerDepend = false;
        foreach ($dependConflictSolution as $env => $depends) {
            if (!$depends) continue;
            if ($env == 'require' || $env == 'require-dev') {
                $delComposerDepend = true;
            } elseif ($env == 'dependencies' || $env == 'devDependencies') {
                $delNpmDepend = true;
            } elseif ($env == 'nuxtDependencies' || $env == 'nuxtDevDependencies') {
                $delNuxtNpmDepend = true;
            }
        }

        // 备份
        $dependJsonFiles   = [
            'composer'       => 'composer.json',
            'webPackage'     => 'web' . DIRECTORY_SEPARATOR . 'package.json',
            'webNuxtPackage' => 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json',
        ];
        $dependWaitInstall = [];
        if ($delComposerDepend) {
            $conflictFile[]      = $dependJsonFiles['composer'];
            $dependWaitInstall[] = [
                'pm'      => false,
                'command' => 'composer.update',
                'type'    => 'composer_dependent_wait_install',
            ];
        }
        if ($delNpmDepend) {
            $conflictFile[]      = $dependJsonFiles['webPackage'];
            $dependWaitInstall[] = [
                'pm'      => true,
                'command' => 'web-install',
                'type'    => 'npm_dependent_wait_install',
            ];
        }
        if ($delNuxtNpmDepend) {
            $conflictFile[]      = $dependJsonFiles['webNuxtPackage'];
            $dependWaitInstall[] = [
                'pm'      => true,
                'command' => 'nuxt-install',
                'type'    => 'nuxt_npm_dependent_wait_install',
            ];
        }
        if ($conflictFile) {
            // 如果是模块自带文件需要备份，加上模块目录前缀
            $overwriteDir = Server::getOverwriteDir();
            foreach ($conflictFile as $key => $item) {
                $paths = explode(DIRECTORY_SEPARATOR, $item);
                if (in_array($paths[0], $overwriteDir) || in_array($item, $dependJsonFiles)) {
                    $conflictFile[$key] = $item;
                } else {
                    $conflictFile[$key] = Filesystem::fsFit(str_replace(root_path(), '', $this->modulesDir . $item));
                }
                if (!is_file(root_path() . $conflictFile[$key])) {
                    unset($conflictFile[$key]);
                }
            }
            $backupsZip = $this->backupsDir . $this->uid . '-disable-' . date('YmdHis') . '.zip';
            Filesystem::zip($conflictFile, $backupsZip);
        }

        // 删除依赖
        $serverDepend = new Depends(root_path() . 'composer.json', 'composer');
        $webDep       = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $webNuxtDep   = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        foreach ($dependConflictSolution as $env => $depends) {
            if (!$depends) continue;
            $dev = !(stripos($env, 'dev') === false);
            if ($env == 'require' || $env == 'require-dev') {
                $serverDepend->removeDepends($depends, $dev);
            } elseif ($env == 'dependencies' || $env == 'devDependencies') {
                $webDep->removeDepends($depends, $dev);
            } elseif ($env == 'nuxtDependencies' || $env == 'nuxtDevDependencies') {
                $webNuxtDep->removeDepends($depends, $dev);
            }
        }

        // 配置了不删除的文件
        $protectedFiles = Server::getConfig($this->modulesDir, 'protectedFiles');
        foreach ($protectedFiles as &$protectedFile) {
            $protectedFile = Filesystem::fsFit(root_path() . $protectedFile);
        }
        // 模块文件列表
        $moduleFile = Server::getFileList($this->modulesDir);

        // 删除模块文件
        foreach ($moduleFile as &$file) {
            // 纯净模式下，模块文件将被删除，此处直接检查模块目录中是否有该文件并恢复（不检查是否开启纯净模式，因为开关可能被调整）
            $moduleFilePath = Filesystem::fsFit($this->modulesDir . $file);
            $file           = Filesystem::fsFit(root_path() . $file);
            if (!file_exists($file)) continue;
            if (!file_exists($moduleFilePath)) {
                if (!is_dir(dirname($moduleFilePath))) {
                    mkdir(dirname($moduleFilePath), 0755, true);
                }
                copy($file, $moduleFilePath);
            }

            if (in_array($file, $protectedFiles)) {
                continue;
            }
            if (file_exists($file)) {
                unlink($file);
            }
            Filesystem::delEmptyDir(dirname($file));
        }

        // 恢复备份文件
        if ($zipDir) {
            $unrecoverableFiles = [
                Filesystem::fsFit(root_path() . 'composer.json'),
                Filesystem::fsFit(root_path() . 'web/package.json'),
                Filesystem::fsFit(root_path() . 'web-nuxt/package.json'),
            ];
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($zipDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                ) as $item
            ) {
                $backupsFile = Filesystem::fsFit(root_path() . str_replace($zipDir, '', $item->getPathname()));

                // 在模块包中，同时不在 $protectedFiles 列表的文件不恢复，这些文件可能是模块升级时备份的
                if (in_array($backupsFile, $moduleFile) && !in_array($backupsFile, $protectedFiles)) {
                    continue;
                }

                if ($item->isDir()) {
                    if (!is_dir($backupsFile)) {
                        mkdir($backupsFile, 0755, true);
                    }
                } else {
                    if (!in_array($backupsFile, $unrecoverableFiles)) {
                        copy($item, $backupsFile);
                    }
                }
            }
        }

        // 删除解压后的备份文件
        Filesystem::delDir($zipDir);

        // 卸载 WebBootstrap
        Server::uninstallWebBootstrap($this->uid);

        $this->setInfo([
            'state' => self::DISABLE,
        ]);

        if ($update) {
            $token = request()->post("token/s", '');
            $order = request()->post("order/d", 0);
            $this->update($token, $order);
            throw new Exception('update', -3, [
                'uid' => $this->uid,
            ]);
        }

        if ($dependWaitInstall) {
            throw new Exception('dependent wait install', -2, [
                'uid'          => $this->uid,
                'wait_install' => $dependWaitInstall,
            ]);
        }
        return $info;
    }

    /**
     * 处理依赖和文件冲突，并完成与前端的冲突处理交互
     * @throws Throwable
     */
    public function conflictHandle(string $trigger): bool
    {
        $info = $this->getInfo();
        if ($info['state'] != self::WAIT_INSTALL && $info['state'] != self::CONFLICT_PENDING) {
            return false;
        }
        $fileConflict   = Server::getFileList($this->modulesDir, true);// 文件冲突
        $dependConflict = Server::dependConflictCheck($this->modulesDir);// 依赖冲突
        $installFiles   = Server::getFileList($this->modulesDir);// 待安装文件
        $depends        = Server::getDepend($this->modulesDir);// 待安装依赖

        $coverFiles   = [];// 要覆盖的文件-备份
        $discardFiles = [];// 抛弃的文件-复制时不覆盖
        $serverDep    = new Depends(root_path() . 'composer.json', 'composer');
        $webDep       = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $webNuxtDep   = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        if ($fileConflict || !self::isEmptyArray($dependConflict)) {
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
                        $oldDepend = '';
                        if (in_array($env, ['require', 'require-dev'])) {
                            $oldDepend = $depend . ' ' . $serverDep->hasDepend($depend, $dev);
                        } elseif (in_array($env, ['dependencies', 'devDependencies'])) {
                            $oldDepend = $depend . ' ' . $webDep->hasDepend($depend, $dev);
                        } elseif (in_array($env, ['nuxtDependencies', 'nuxtDevDependencies'])) {
                            $oldDepend = $depend . ' ' . $webNuxtDep->hasDepend($depend, $dev);
                        }
                        $dependConflictTemp[] = [
                            'env'       => $env,
                            'newDepend' => $depend . ' ' . $v,
                            'oldDepend' => $oldDepend,
                            'depend'    => $depend,
                            'solution'  => 'cover',
                        ];
                    }
                }
                $this->setInfo([
                    'state' => self::CONFLICT_PENDING,
                ]);
                throw new Exception('Module file conflicts', -1, [
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
            if (!self::isEmptyArray($dependConflict) && isset($extend['dependConflict'])) {
                foreach ($depends as $fKey => $fItem) {
                    foreach ($fItem as $cKey => $cItem) {
                        if (isset($extend['dependConflict'][$fKey][$cKey])) {
                            if ($extend['dependConflict'][$fKey][$cKey] == 'discard') {
                                unset($depends[$fKey][$cKey]);
                            }
                        }
                    }
                }
            }
        }

        // 如果有依赖更新，增加要备份的文件
        if ($depends) {
            foreach ($depends as $key => $item) {
                if (!$item) {
                    continue;
                }
                if ($key == 'require' || $key == 'require-dev') {
                    $coverFiles[] = 'composer.json';
                    continue;
                }
                if ($key == 'dependencies' || $key == 'devDependencies') {
                    $coverFiles[] = 'web' . DIRECTORY_SEPARATOR . 'package.json';
                }
                if ($key == 'nuxtDependencies' || $key == 'nuxtDevDependencies') {
                    $coverFiles[] = 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json';
                }
            }
        }

        // 备份将被覆盖的文件
        if ($coverFiles) {
            $backupsZip = $trigger == 'install' ? $this->backupsDir . $this->uid . '-install.zip' : $this->backupsDir . $this->uid . '-cover-' . date('YmdHis') . '.zip';
            Filesystem::zip($coverFiles, $backupsZip);
        }

        if ($depends) {
            $npm      = false;
            $composer = false;
            $nuxtNpm  = false;
            foreach ($depends as $key => $item) {
                if (!$item) {
                    continue;
                }
                if ($key == 'require') {
                    $composer = true;
                    $serverDep->addDepends($item, false, true);
                } elseif ($key == 'require-dev') {
                    $composer = true;
                    $serverDep->addDepends($item, true, true);
                } elseif ($key == 'dependencies') {
                    $npm = true;
                    $webDep->addDepends($item, false, true);
                } elseif ($key == 'devDependencies') {
                    $npm = true;
                    $webDep->addDepends($item, true, true);
                } elseif ($key == 'nuxtDependencies') {
                    $nuxtNpm = true;
                    $webNuxtDep->addDepends($item, false, true);
                } elseif ($key == 'nuxtDevDependencies') {
                    $nuxtNpm = true;
                    $webNuxtDep->addDepends($item, true, true);
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
            if ($nuxtNpm) {
                $info['nuxt_npm_dependent_wait_install'] = 1;
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

        // 复制文件
        $overwriteDir = Server::getOverwriteDir();
        foreach ($overwriteDir as $dirItem) {
            $baseDir = $this->modulesDir . $dirItem;
            $destDir = root_path() . $dirItem;
            if (!is_dir($baseDir)) {
                continue;
            }
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                ) as $item
            ) {
                $destDirItem = Filesystem::fsFit($destDir . DIRECTORY_SEPARATOR . str_replace($baseDir, '', $item->getPathname()));
                if ($item->isDir()) {
                    Filesystem::mkdir($destDirItem);
                } else {
                    if (!in_array(str_replace(root_path(), '', $destDirItem), $discardFiles)) {
                        Filesystem::mkdir(dirname($destDirItem));
                        copy($item, $destDirItem);
                    }
                }
            }
            // 纯净模式
            if (Config::get('buildadmin.module_pure_install')) {
                Filesystem::delDir($baseDir);
            }
        }
        return true;
    }

    /**
     * 依赖升级处理
     * @throws Throwable
     */
    public function dependUpdateHandle(): void
    {
        $info = $this->getInfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            $waitInstall = [];
            if (isset($info['composer_dependent_wait_install'])) {
                $waitInstall[] = 'composer_dependent_wait_install';
            }
            if (isset($info['npm_dependent_wait_install'])) {
                $waitInstall[] = 'npm_dependent_wait_install';
            }
            if (isset($info['nuxt_npm_dependent_wait_install'])) {
                $waitInstall[] = 'nuxt_npm_dependent_wait_install';
            }
            if ($waitInstall) {
                throw new Exception('dependent wait install', -2, [
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
     * 依赖安装完成标记
     * @throws Throwable
     */
    public function dependentInstallComplete(string $type): void
    {
        $info = $this->getInfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            if ($type == 'npm') {
                unset($info['npm_dependent_wait_install']);
            }
            if ($type == 'nuxt_npm') {
                unset($info['nuxt_npm_dependent_wait_install']);
            }
            if ($type == 'composer') {
                unset($info['composer_dependent_wait_install']);
            }
            if ($type == 'all') {
                unset($info['npm_dependent_wait_install'], $info['composer_dependent_wait_install'], $info['nuxt_npm_dependent_wait_install']);
            }
            if (!isset($info['npm_dependent_wait_install']) && !isset($info['composer_dependent_wait_install']) && !isset($info['nuxt_npm_dependent_wait_install'])) {
                $info['state'] = self::INSTALLED;
            }
            $this->setInfo([], $info);
        }
    }

    /**
     * 禁用依赖检查
     * @throws Throwable
     */
    public function disableDependCheck(): array
    {
        // 读取模块所有依赖
        $depend = Server::getDepend($this->modulesDir);
        if (!$depend) {
            return [];
        }

        // 读取所有依赖中，系统上已经安装的依赖
        $serverDep  = new Depends(root_path() . 'composer.json', 'composer');
        $webDep     = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $webNuxtDep = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        foreach ($depend as $key => $depends) {
            $dev = !(stripos($key, 'dev') === false);
            if ($key == 'require' || $key == 'require-dev') {
                foreach ($depends as $dependKey => $dependItem) {
                    if (!$serverDep->hasDepend($dependKey, $dev)) {
                        unset($depends[$dependKey]);
                    }
                }
                $depend[$key] = $depends;
            } elseif ($key == 'dependencies' || $key == 'devDependencies') {
                foreach ($depends as $dependKey => $dependItem) {
                    if (!$webDep->hasDepend($dependKey, $dev)) {
                        unset($depends[$dependKey]);
                    }
                }
                $depend[$key] = $depends;
            } elseif ($key == 'nuxtDependencies' || $key == 'nuxtDevDependencies') {
                foreach ($depends as $dependKey => $dependItem) {
                    if (!$webNuxtDep->hasDepend($dependKey, $dev)) {
                        unset($depends[$dependKey]);
                    }
                }
                $depend[$key] = $depends;
            }
        }
        return $depend;
    }

    /**
     * 检查包是否完整
     * @throws Throwable
     */
    public function checkPackage(): bool
    {
        if (!is_dir($this->modulesDir)) {
            throw new Exception('Module package file does not exist');
        }
        $info     = $this->getInfo();
        $infoKeys = ['uid', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($infoKeys as $value) {
            if (!array_key_exists($value, $info)) {
                Filesystem::delDir($this->modulesDir);
                throw new Exception('Basic configuration of the Module is incomplete');
            }
        }
        return true;
    }

    /**
     * 获取模块基本信息
     */
    public function getInfo(): array
    {
        return Server::getIni($this->modulesDir);
    }

    /**
     * 设置模块基本信息
     * @throws Throwable
     */
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


    /**
     * 检查多维数组是否全部为空
     */
    public static function isEmptyArray($arr): bool
    {
        foreach ($arr as $item) {
            if (is_array($item)) {
                $empty = self::isEmptyArray($item);
                if (!$empty) return false;
            } elseif ($item) {
                return false;
            }
        }
        return true;
    }
}