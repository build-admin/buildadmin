<?php

namespace app\admin\command;

use FilesystemIterator;
use think\console\Input;
use think\console\Output;
use think\console\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use app\admin\library\module\Server;
use RecursiveCallbackFilterIterator;

class FilesWatch extends Command
{

    /**
     * 需要同步文件改变的目录
     */
    protected array $syncDirs = [];

    protected function initialize(Input $input, Output $output): void
    {
        parent::initialize($input, $output);
        $modulesRootDir = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        $modules        = Server::installedList($modulesRootDir);

        echo date('[H:i:s]') . " 开始监听系统文件变更以自动同步到对应模块目录（以便重新打包模块）\n\n";
        echo date('[H:i:s]') . " -------------------- 系统中来自以下模块的文件修改时将被自动同步至对应模块目录（完整安装模式的模块） --------------------\n";

        foreach ($modules as $module) {
            $moduleDir = $modulesRootDir . $module['uid'] . DIRECTORY_SEPARATOR;
            $pure      = Server::getRuntime($moduleDir, 'pure');
            if (!$pure) {
                $this->syncDirs[] = $moduleDir;
                echo date('[H:i:s]') . " {$module['uid']} => $moduleDir\n";
            }
        }

        echo date('[H:i:s]') . " ------------------------------------------------------------------------------------------------------------------------\n\n";
    }

    protected function configure(): void
    {
        $this->setName('FilesWatch')->setDescription('Files watch');
    }

    protected function execute(Input $input, Output $output): void
    {
        set_time_limit(0);
        $rootPath    = root_path();
        $rootDirName = explode(DIRECTORY_SEPARATOR, $rootPath);
        $rootDirName = $rootDirName[count($rootDirName) - 2];

        $filter   = fn($file) => !preg_match("/(\.git|node_modules|\.nuxt|\.idea|$rootDirName.vendor|$rootDirName.runtime|$rootDirName.modules|public.assets|public.install|public.npm-install-test|public.storage.default)/", $file->getPathname());
        $rootDir  = new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS);
        $filter   = new RecursiveCallbackFilterIterator($rootDir, $filter);
        $iterator = new RecursiveIteratorIterator($filter);

        echo date('[H:i:s]') . " ---- 以下为模块文件同步日志（删除或新增文件并不会自动同步，请手动处理），每5秒检测同步一次，按下 Ctrl+C 终止检测进程 ---\n";

        while (true) {
            foreach ($iterator as $item) {
                $fullPathName = $item->getPathname();
                $pathName     = str_replace($rootPath, '', $fullPathName);
                foreach ($this->syncDirs as $syncDir) {
                    $syncFile = $syncDir . $pathName;
                    if (is_file($syncFile) && (filesize($syncFile) != filesize($fullPathName) || md5_file($syncFile) != md5_file($fullPathName))) {
                        copy($fullPathName, $syncFile);
                        echo date('[H:i:s]') . ' ' . str_replace($rootPath, '', $syncFile) . " 已同步为系统文件~\n";
                    }
                }
            }
            sleep(5);
        }
    }
}