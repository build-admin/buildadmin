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
    public const DISABLE                = 6;

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
        if ($state == self::INSTALLED || $state == self::DIRECTORY_OCCUPIED || $state == self::DISABLE) {
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

            // 设置为待安装状态
            $this->setInfo([
                'state' => self::WAIT_INSTALL,
            ]);
        }

        // 检查是否完整
        $this->checkPackage();

        // 导入sql
        Server::importSql($this->modulesDir);

        // 启用插件
        $this->enable('install');

        return $this->getInfo();
    }

    public function changeState(bool $state)
    {
        $info       = $this->getInfo();
        $canDisable = [
            self::INSTALLED,
            self::CONFLICT_PENDING,
            self::DEPENDENT_WAIT_INSTALL,
        ];
        if (!$state) {
            if (!in_array($info['state'], $canDisable)) {
                throw new moduleException('The current state of the module cannot be set to disabled', 0, [
                    'uid'   => $this->uid,
                    'state' => $info['state'],
                ]);
            }
            $this->disable();
            return;
        }

        if ($info['state'] != self::DISABLE) {
            throw new moduleException('The current state of the module cannot be set to enabled', 0, [
                'uid'   => $this->uid,
                'state' => $info['state'],
            ]);
        }
        $this->enable('enable');
    }

    public function enable(string $trigger)
    {
        $this->conflictHandle($trigger);
        $this->dependUpdateHandle();

        // 执行启用脚本
        Server::execEvent($this->uid, 'enable');
    }

    public function disable()
    {
        /**
         * 0、找到模块添加的文件，对有修改的进行备份
         * 1、找到模块添加的文件，删除
         * 2、找到安装时备份的文件，恢复
         * 3、执行禁用脚本
         * 4、将模块设置为已禁用
         * 5、如果有依赖调整，执行依赖更新/安装命令
         */

        $zipFile = $this->ebakDir . $this->uid . '-install.zip';
        try {
            $zipDir = Server::unzip($zipFile);
        } catch (moduleException|Exception $e) {
            $zipDir = false;
        }

        $confirmConflict       = request()->get("confirmConflict/b", false);
        $conflictFile          = Server::getFileList($this->modulesDir, true);
        $disableDependConflict = $this->disableDependConflictCheck($zipDir);
        if (($disableDependConflict || $conflictFile) && !$confirmConflict) {
            throw new moduleException('Module file updated', -1, [
                'uid'            => $this->uid,
                'conflictFile'   => $conflictFile,
                'dependConflict' => (bool)$disableDependConflict,
            ]);
        }

        // 对冲突进行备份
        if (in_array('composer', $disableDependConflict)) {
            $conflictFile[] = 'composer.json';
        }
        if (in_array('npm', $disableDependConflict)) {
            $conflictFile[] = 'web' . DIRECTORY_SEPARATOR . 'package.json';
        }
        if ($conflictFile) {
            $ebakZip = $this->ebakDir . $this->uid . '-disable-' . date('YmdHis') . '.zip';
            Server::createZip($conflictFile, $ebakZip);
        }

        // 删除模块文件
        $protectedFiles = Server::getConfig($this->modulesDir, 'protectedFiles');
        foreach ($protectedFiles as &$protectedFile) {
            $protectedFile = path_transform(root_path() . $protectedFile);
        }
        $moduleFile = Server::getFileList($this->modulesDir);
        foreach ($moduleFile as $item) {
            $file = path_transform(root_path() . $item);
            if (in_array($file, $protectedFiles)) {
                continue;
            }
            if (file_exists($file)) {
                unlink($file);
            }
            del_empty_dir(dirname($file));
        }

        // 恢复备份文件
        if ($zipDir) {
            foreach (
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($zipDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                ) as $item
            ) {
                $ebakFile = path_transform(root_path() . $iterator->getSubPathName());
                if ($item->isDir()) {
                    if (!is_dir($ebakFile)) {
                        mkdir($ebakFile, 0755, true);
                    }
                } else {
                    copy($item, $ebakFile);
                }
            }
        }

        deldir($zipDir);
    }

    /**
     * 处理依赖和文件冲突，并完成与前端的冲突处理交互
     * @throws moduleException|Exception
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
            }
        }

        // 备份将被覆盖的文件
        if ($coverFiles) {
            $ebakZip = $trigger == 'install' ? $this->ebakDir . $this->uid . '-install.zip' : $this->ebakDir . $this->uid . '-cover-' . date('YmdHis') . '.zip';
            Server::createZip($coverFiles, $ebakZip);
        }

        if ($depends) {
            $npm      = false;
            $composer = false;
            foreach ($depends as $key => $item) {
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
        return true;
    }

    public function dependUpdateHandle()
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
            if ($waitInstall) {
                throw new moduleException('dependent wait install', -2, [
                    'uid'          => $this->uid,
                    'state'        => self::DEPENDENT_WAIT_INSTALL,
                    'wait_install' => $waitInstall,
                    'fullreload'   => $info['fullreload'],
                ]);
            } else {
                $this->setInfo([
                    'state' => self::INSTALLED,
                ]);
            }
        }
    }

    public function dependentInstallComplete(string $type)
    {
        $info = $this->getInfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            if ($type == 'npm') {
                unset($info['npm_dependent_wait_install']);
            }
            if ($type == 'composer') {
                unset($info['composer_dependent_wait_install']);
            }
            if ($type == 'all') {
                unset($info['npm_dependent_wait_install'], $info['composer_dependent_wait_install']);
            }
            if (!isset($info['npm_dependent_wait_install']) && !isset($info['composer_dependent_wait_install'])) {
                $info['state'] = self::INSTALLED;
            }
            $this->setInfo([], $info);
        }
    }

    public function disableDependConflictCheck(string $ebakDir): array
    {
        if (!$ebakDir) {
            return [];
        }
        $dependFile = [
            path_transform($ebakDir . DIRECTORY_SEPARATOR . 'composer.json')    => [
                'path' => path_transform(root_path() . 'composer.json'),
                'type' => 'composer',
            ],
            path_transform($ebakDir . DIRECTORY_SEPARATOR . 'web/package.json') => [
                'path' => path_transform(root_path() . 'web/package.json'),
                'type' => 'npm',
            ],
        ];
        $conflict   = [];
        foreach ($dependFile as $key => $item) {
            if (is_file($key) && (filesize($key) != filesize($item['path']) || md5_file($key) != md5_file($item['path']))) {
                $conflict[] = $item['type'];
            }
        }
        return $conflict;
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