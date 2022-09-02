<?php

namespace ba\module;

use ba\Depend;
use PhpZip\ZipFile;
use think\Exception;
use think\facade\Db;
use GuzzleHttp\Client;
use think\facade\Config;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpZip\Exception\ZipException;
use GuzzleHttp\Exception\TransferException;
use think\db\exception\PDOException;

/**
 * 模块服务类
 */
class Server
{
    private static $client = null;

    public static function download(string $uid, string $dir, array $extend = []): string
    {
        $tmpFile = $dir . $uid . ".zip";
        try {
            $client   = self::getClient();
            $response = $client->get('/api/store/download', ['query' => array_merge(['uid' => $uid, 'server' => 1], $extend)]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            if ($content == '' || stripos($content, '<title>系统发生错误</title>') !== false) {
                throw new moduleException('package download failed', 0);
            }
            if (substr($content, 0, 1) === '{') {
                $json = (array)json_decode($content, true);
                throw new moduleException($json['msg'], $json['code'], $json['data']);
            }
        } catch (TransferException $e) {
            throw new moduleException('package download failed', 0, ['msg' => $e->getMessage()]);
        }

        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
    }

    public static function unzip(string $file, string $dir = ''): string
    {
        if (!file_exists($file)) {
            throw new Exception("Zip file not found");
        }

        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (ZipException $e) {
            $zip->close();
            throw new moduleException('Unable to open the zip file', 0, ['msg' => $e->getMessage()]);
        }

        $dir = $dir ?: rtrim($file, '.zip');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755);
        }

        try {
            $zip->extractTo($dir);
        } catch (ZipException $e) {
            throw new moduleException('Unable to extract ZIP file', 0, ['msg' => $e->getMessage()]);
        } finally {
            $zip->close();
        }
        return $dir;
    }

    public static function getConfig(string $dir, $key = '')
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

    public static function getDepend(string $dir, $key = '')
    {
        if ($key) {
            return self::getConfig($dir, $key);
        }
        $configContent = self::getConfig($dir);
        $dependKey     = ['require', 'require-dev', 'dependencies', 'devDependencies'];
        $dependArray   = [];
        foreach ($dependKey as $item) {
            if (array_key_exists($item, $configContent) && $configContent[$item]) {
                $dependArray[$item] = $configContent[$item];
            }
        }
        return $dependArray;
    }

    public static function dependConflictCheck(string $dir): array
    {
        $depend    = self::getDepend($dir);
        $dependObj = new Depend();
        $sysDepend = [
            'require'         => $dependObj->getComposerRequire(),
            'require-dev'     => $dependObj->getComposerRequire(true),
            'dependencies'    => $dependObj->getNpmDependencies(),
            'devDependencies' => $dependObj->getNpmDependencies(true),
        ];

        $conflict = [];
        foreach ($depend as $key => $item) {
            $conflict[$key] = array_uintersect_assoc($item, $sysDepend[$key], function ($a, $b) {
                return $a == $b ? -1 : 0;
            });
        }
        return $conflict;
    }

    public static function createZip(array $files, string $fileName): bool
    {
        $zip = new ZipFile();
        try {
            foreach ($files as $v) {
                $zip->addFile(root_path() . $v, $v);
            }
            $zip->saveAsFile($fileName);
        } catch (ZipException $e) {
            throw new moduleException('Unable to package zip file', 0, ['msg' => $e->getMessage(), 'file' => $fileName]);
        } finally {
            $zip->close();
        }
        return true;
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
            'extend',
            'public',
            'vendor',
            'web',
        ];
    }

    public static function importSql(string $dir): bool
    {
        $sqlFile  = $dir . 'install.sql';
        $tempLine = '';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $tempLine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $tempLine = str_ireplace('__PREFIX__', Config::get('database.connections.mysql.prefix'), $tempLine);
                    $tempLine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $tempLine);
                    try {
                        Db::execute($tempLine);
                    } catch (PDOException $e) {
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

    public static function getIni($dir)
    {
        $infoFile = $dir . 'info.ini';
        $info     = [];
        if (is_file($infoFile)) {
            $info = parse_ini_file($infoFile, true, INI_SCANNER_TYPED) ?: [];
        }
        return $info;
    }

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

    public static function getClass(string $uid, string $type = 'hook', string $class = null): string
    {
        $name = parse_name($uid);
        if (!is_null($class) && strpos($class, '.')) {
            $class                    = explode('.', $class);
            $class[count($class) - 1] = parse_name(end($class), 1);
            $class                    = implode('\\', $class);
        } else {
            $class = parse_name(is_null($class) ? $name : $class, 1);
        }
        switch ($type) {
            case 'controller':
                $namespace = '\\modules\\' . $name . '\\controller\\' . $class;
                break;
            default:
                $namespace = '\\modules\\' . $name . '\\' . $class;
        }
        return class_exists($namespace) ? $namespace : '';
    }

    public static function execEvent(string $uid, string $event)
    {
        $eventClass = self::getClass($uid);
        if (class_exists($eventClass)) {
            $handle = new $eventClass();
            if (method_exists($eventClass, $event)) {
                $handle->$event();
            }
        }
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