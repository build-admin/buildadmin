<?php

namespace app\admin\library\module;

use Throwable;
use ba\Depends;
use ba\Exception;
use ba\Filesystem;
use think\facade\Db;
use GuzzleHttp\Client;
use FilesystemIterator;
use think\facade\Config;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use think\db\exception\PDOException;
use app\admin\library\crud\Helper;
use GuzzleHttp\Exception\TransferException;

/**
 * 模块服务类
 */
class Server
{
    private static ?Client $client = null;

    /**
     * 下载
     * @throws Throwable
     */
    public static function download(string $uid, string $dir, array $extend = []): string
    {
        $tmpFile = $dir . $uid . ".zip";
        try {
            $client   = self::getClient();
            $response = $client->get('/api/v6.store/download', ['query' => array_merge(['uid' => $uid, 'server' => 1], $extend)]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            if ($content == '' || stripos($content, '<title>系统发生错误</title>') !== false) {
                throw new Exception('package download failed', 0);
            }
            if (str_starts_with($content, '{')) {
                $json = (array)json_decode($content, true);
                throw new Exception($json['msg'], $json['code'], $json['data'] ?? []);
            }
        } catch (TransferException $e) {
            throw new Exception('package download failed', 0, ['msg' => $e->getMessage()]);
        }

        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
    }

    public static function getConfig(string $dir, $key = ''): array
    {
        $configFile = $dir . 'config.json';
        if (!is_dir($dir) || !is_file($configFile)) {
            return [];
        }
        $configContent = @file_get_contents($configFile);
        $configContent = json_decode($configContent, true);
        if (!$configContent) {
            return [];
        }
        if ($key) {
            return $configContent[$key] ?? [];
        }
        return $configContent;
    }

    public static function getDepend(string $dir, string $key = ''): array
    {
        if ($key) {
            return self::getConfig($dir, $key);
        }
        $configContent = self::getConfig($dir);
        $dependKey     = ['require', 'require-dev', 'dependencies', 'devDependencies', 'nuxtDependencies', 'nuxtDevDependencies'];
        $dependArray   = [];
        foreach ($dependKey as $item) {
            if (array_key_exists($item, $configContent) && $configContent[$item]) {
                $dependArray[$item] = $configContent[$item];
            }
        }
        return $dependArray;
    }

    /**
     * 依赖冲突检查
     * @throws Throwable
     */
    public static function dependConflictCheck(string $dir): array
    {
        $depend     = self::getDepend($dir);
        $serverDep  = new Depends(root_path() . 'composer.json', 'composer');
        $webDep     = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $webNuxtDep = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        $sysDepend  = [
            'require'             => $serverDep->getDepends(),
            'require-dev'         => $serverDep->getDepends(true),
            'dependencies'        => $webDep->getDepends(),
            'devDependencies'     => $webDep->getDepends(true),
            'nuxtDependencies'    => $webNuxtDep->getDepends(),
            'nuxtDevDependencies' => $webNuxtDep->getDepends(true),
        ];

        $conflict = [];
        foreach ($depend as $key => $item) {
            $conflict[$key] = array_uintersect_assoc($item, $sysDepend[$key], function ($a, $b) {
                return $a == $b ? -1 : 0;
            });
        }
        return $conflict;
    }

    public static function getFileList(string $dir, bool $onlyConflict = false): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $fileList     = [];
        $overwriteDir = self::getOverwriteDir();
        foreach ($overwriteDir as $item) {
            $baseDir = $dir . $item;
            if (!is_dir($baseDir)) {
                continue;
            }
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getPathName();
                    $path     = str_replace($dir, '', $filePath);
                    $path     = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

                    if ($onlyConflict) {
                        $overwriteFile = root_path() . $path;
                        if (is_file($overwriteFile) && (filesize($overwriteFile) != filesize($filePath) || md5_file($overwriteFile) != md5_file($filePath))) {
                            $fileList[] = $path;
                        }
                    } else {
                        $fileList[] = $path;
                    }
                }
            }
        }
        return $fileList;
    }

    public static function getOverwriteDir(): array
    {
        return [
            'app',
            'config',
            'database',
            'extend',
            'public',
            'vendor',
            'web',
            'web-nuxt',
        ];
    }

    public static function importSql(string $dir): bool
    {
        $sqlFile  = $dir . 'install.sql';
        $tempLine = '';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            foreach ($lines as $line) {
                if (str_starts_with($line, '--') || $line == '' || str_starts_with($line, '/*')) {
                    continue;
                }

                $tempLine .= $line;
                if (str_ends_with(trim($line), ';')) {
                    $tempLine = str_ireplace('__PREFIX__', Config::get('database.connections.mysql.prefix'), $tempLine);
                    $tempLine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $tempLine);
                    try {
                        Db::execute($tempLine);
                    } catch (PDOException) {
                        // $e->getMessage();
                    }
                    $tempLine = '';
                }
            }
        }
        return true;
    }

    public static function installedList(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $installedDir  = scandir($dir);
        $installedList = [];
        foreach ($installedDir as $item) {
            if ($item === '.' or $item === '..' || is_file($dir . $item)) {
                continue;
            }
            $tempDir = $dir . $item . DIRECTORY_SEPARATOR;
            if (!is_dir($tempDir)) {
                continue;
            }
            $info = self::getIni($tempDir);
            if (!isset($info['uid'])) {
                continue;
            }
            $installedList[] = $info;
        }
        return $installedList;
    }

    /**
     * 获取模块ini
     * @param string $dir 模块目录路径
     */
    public static function getIni(string $dir): array
    {
        $infoFile = $dir . 'info.ini';
        $info     = [];
        if (is_file($infoFile)) {
            $info = parse_ini_file($infoFile, true, INI_SCANNER_TYPED) ?: [];
            if (!$info) return [];
        }
        return $info;
    }

    /**
     * 设置模块ini
     * @param string $dir 模块目录路径
     * @param array  $arr 新的ini数据
     * @return bool
     * @throws Throwable
     */
    public static function setIni(string $dir, array $arr): bool
    {
        $infoFile = $dir . 'info.ini';
        $ini      = [];
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $ini[] = "[$key]";
                foreach ($val as $ikey => $ival) {
                    $ini[] = "$ikey = $ival";
                }
            } else {
                $ini[] = "$key = $val";
            }
        }
        if (!file_put_contents($infoFile, implode("\n", $ini) . "\n", LOCK_EX)) {
            throw new Exception("Configuration file has no write permission");
        }
        return true;
    }

    public static function getClass(string $uid, string $type = 'event', string $class = null): string
    {
        $name = parse_name($uid);
        if (!is_null($class) && strpos($class, '.')) {
            $class                    = explode('.', $class);
            $class[count($class) - 1] = parse_name(end($class), 1);
            $class                    = implode('\\', $class);
        } else {
            $class = parse_name(is_null($class) ? $name : $class, 1);
        }
        $namespace = match ($type) {
            'controller' => '\\modules\\' . $name . '\\controller\\' . $class,
            default => '\\modules\\' . $name . '\\' . $class,
        };
        return class_exists($namespace) ? $namespace : '';
    }

    public static function execEvent(string $uid, string $event, array $params = []): void
    {
        $eventClass = self::getClass($uid);
        if (class_exists($eventClass)) {
            $handle = new $eventClass();
            if (method_exists($eventClass, $event)) {
                $handle->$event($params);
            }
        }
    }

    /**
     * 分析 WebBootstrap 代码
     */
    public static function analysisWebBootstrap(string $uid, string $dir): array
    {
        $bootstrapFile = $dir . 'webBootstrap.stub';
        if (!file_exists($bootstrapFile)) return [];
        $bootstrapContent = file_get_contents($bootstrapFile);
        $pregArr          = [
            'mainTsImport'    => '/#main.ts import code start#([\s\S]*?)#main.ts import code end#/i',
            'mainTsStart'     => '/#main.ts start code start#([\s\S]*?)#main.ts start code end#/i',
            'appVueImport'    => '/#App.vue import code start#([\s\S]*?)#App.vue import code end#/i',
            'appVueOnMounted' => '/#App.vue onMounted code start#([\s\S]*?)#App.vue onMounted code end#/i',
        ];
        $codeStrArr       = [];
        foreach ($pregArr as $key => $item) {
            preg_match($item, $bootstrapContent, $matches);
            if (isset($matches[1]) && $matches[1]) {
                $mainImportCodeArr = array_filter(preg_split('/\r\n|\r|\n/', $matches[1]));
                if ($mainImportCodeArr) {
                    $codeStrArr[$key] = "\n";
                    if (count($mainImportCodeArr) == 1) {
                        foreach ($mainImportCodeArr as $codeItem) {
                            $codeStrArr[$key] .= $codeItem . self::buildMarkStr('module-line-mark', $uid, $key);
                        }
                    } else {
                        $codeStrArr[$key] .= self::buildMarkStr('module-multi-line-mark-start', $uid, $key);
                        foreach ($mainImportCodeArr as $codeItem) {
                            $codeStrArr[$key] .= $codeItem . "\n";
                        }
                        $codeStrArr[$key] .= self::buildMarkStr('module-multi-line-mark-end', $uid, $key);
                    }
                }
            }
            unset($matches);
        }

        return $codeStrArr;
    }

    /**
     * 安装 WebBootstrap
     */
    public static function installWebBootstrap(string $uid, string $dir): void
    {
        $mainTsKeys    = ['mainTsImport', 'mainTsStart'];
        $bootstrapCode = self::analysisWebBootstrap($uid, $dir);
        $basePath      = root_path() . 'web' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

        $marks = [
            'mainTsImport'    => self::buildMarkStr('import-root-mark'),
            'mainTsStart'     => self::buildMarkStr('start-root-mark'),
            'appVueImport'    => self::buildMarkStr('import-root-mark'),
            'appVueOnMounted' => self::buildMarkStr('onMounted-root-mark'),
        ];

        foreach ($bootstrapCode as $key => $item) {
            if ($item && isset($marks[$key])) {
                $filePath = $basePath . (in_array($key, $mainTsKeys) ? 'main.ts' : 'App.vue');
                $content  = file_get_contents($filePath);

                $markPos = stripos($content, $marks[$key]);
                if ($markPos && strripos($content, self::buildMarkStr('module-line-mark', $uid, $key)) === false && strripos($content, self::buildMarkStr('module-multi-line-mark-start', $uid, $key)) === false) {
                    $content = substr_replace($content, $item, $markPos + strlen($marks[$key]), 0);
                    file_put_contents($filePath, $content);
                }
            }
        }
    }

    /**
     * 卸载 WebBootstrap
     */
    public static function uninstallWebBootstrap(string $uid): void
    {
        $mainTsKeys = ['mainTsImport', 'mainTsStart'];
        $basePath   = root_path() . 'web' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $marksKey   = [
            'mainTsImport',
            'mainTsStart',
            'appVueImport',
            'appVueOnMounted',
        ];

        foreach ($marksKey as $item) {
            $filePath                 = $basePath . (in_array($item, $mainTsKeys) ? 'main.ts' : 'App.vue');
            $content                  = file_get_contents($filePath);
            $moduleLineMark           = self::buildMarkStr('module-line-mark', $uid, $item);
            $moduleMultiLineMarkStart = self::buildMarkStr('module-multi-line-mark-start', $uid, $item);
            $moduleMultiLineMarkEnd   = self::buildMarkStr('module-multi-line-mark-end', $uid, $item);

            // 寻找标记，找到则将其中内容删除
            $moduleLineMarkPos = strripos($content, $moduleLineMark);
            if ($moduleLineMarkPos !== false) {
                $delStartTemp = explode($moduleLineMark, $content);
                $delStartPos  = strripos(rtrim($delStartTemp[0], "\n"), "\n");
                $delEndPos    = stripos($content, "\n", $moduleLineMarkPos);
                $content      = substr_replace($content, '', $delStartPos, $delEndPos - $delStartPos);
            }

            $moduleMultiLineMarkStartPos = stripos($content, $moduleMultiLineMarkStart);
            if ($moduleMultiLineMarkStartPos !== false) {
                $moduleMultiLineMarkStartPos--;
                $moduleMultiLineMarkEndPos = stripos($content, $moduleMultiLineMarkEnd);
                $delLang                   = ($moduleMultiLineMarkEndPos + strlen($moduleMultiLineMarkEnd)) - $moduleMultiLineMarkStartPos;
                $content                   = substr_replace($content, '', $moduleMultiLineMarkStartPos, $delLang);
            }

            if ($moduleLineMarkPos || $moduleMultiLineMarkStartPos) {
                file_put_contents($filePath, $content);
            }
        }
    }

    /**
     * 构建 WebBootstrap 需要的各种标记字符串
     * @param string $type
     * @param string $uid    模块UID
     * @param string $extend 扩展数据
     * @return string
     */
    public static function buildMarkStr(string $type, string $uid = '', string $extend = ''): string
    {
        $importKeys = ['mti', 'avi'];
        $extend     = match ($extend) {
            'mainTsImport' => 'mti',
            'mainTsStart' => 'mts',
            'appVueImport' => 'avi',
            'appVueOnMounted' => 'avo',
            default => '',
        };
        return match ($type) {
            'import-root-mark' => '// modules import mark, Please do not remove.',
            'start-root-mark' => '// modules start mark, Please do not remove.',
            'onMounted-root-mark' => '// Modules onMounted mark, Please do not remove.',
            'module-line-mark' => ' // Code from module \'' . $uid . "'" . ($extend ? "($extend)" : ''),
            'module-multi-line-mark-start' => (in_array($extend, $importKeys) ? '' : Helper::tab()) . "// Code from module '$uid' start" . ($extend ? "($extend)" : '') . "\n",
            'module-multi-line-mark-end' => (in_array($extend, $importKeys) ? '' : Helper::tab()) . "// Code from module '$uid' end",
            default => '',
        };
    }

    public static function getNuxtVersion()
    {
        $nuxtPackageJsonPath = Filesystem::fsFit(root_path() . 'web-nuxt/package.json');
        if (is_file($nuxtPackageJsonPath)) {
            $nuxtPackageJson = file_get_contents($nuxtPackageJsonPath);
            $nuxtPackageJson = json_decode($nuxtPackageJson, true);
            if ($nuxtPackageJson && isset($nuxtPackageJson['version'])) {
                return $nuxtPackageJson['version'];
            }
        }
        return false;
    }

    /**
     * 获取请求对象
     * @return Client
     */
    protected static function getClient(): Client
    {
        $options = [
            'base_uri'        => Config::get('buildadmin.api_url'),
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'BuildAdminClient',
            ]
        ];
        if (is_null(self::$client)) {
            self::$client = new Client($options);
        }
        return self::$client;
    }
}