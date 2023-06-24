<?php

namespace ba;

use Throwable;
use PhpZip\ZipFile;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * 访问和操作文件系统
 */
class Filesystem
{
    /**
     * 是否是空目录
     */
    public static function dirIsEmpty(string $dir): bool
    {
        if (!file_exists($dir)) return true;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * 递归删除目录
     * @param string $dir     目录路径
     * @param bool   $delSelf 是否删除传递的目录本身
     * @return bool
     */
    public static function delDir(string $dir, bool $delSelf = true): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileInfo) {
            if ($fileInfo->isDir()) {
                self::delDir($fileInfo->getRealPath());
            } else {
                @unlink($fileInfo->getRealPath());
            }
        }
        if ($delSelf) {
            @rmdir($dir);
        }
        return true;
    }

    /**
     * 解压Zip
     * @param string $file ZIP文件路径
     * @param string $dir  解压路径
     * @return string 解压后的路径
     * @throws Throwable
     */
    public static function unzip(string $file, string $dir = ''): string
    {
        if (!file_exists($file)) {
            throw new Exception("Zip file not found");
        }

        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (Throwable $e) {
            $zip->close();
            throw new Exception('Unable to open the zip file', 0, ['msg' => $e->getMessage()]);
        }

        $dir = $dir ?: substr($file, 0, strripos($file, '.zip'));
        if (!is_dir($dir)) {
            @mkdir($dir, 0755);
        }

        try {
            $zip->extractTo($dir);
        } catch (Throwable $e) {
            throw new Exception('Unable to extract ZIP file', 0, ['msg' => $e->getMessage()]);
        } finally {
            $zip->close();
        }
        return $dir;
    }

    /**
     * 创建ZIP
     * @param array  $files    文件路径列表
     * @param string $fileName ZIP文件名称
     * @return bool
     * @throws Throwable
     */
    public static function zip(array $files, string $fileName): bool
    {
        $zip = new ZipFile();
        try {
            foreach ($files as $v) {
                $zip->addFile(root_path() . $v, $v);
            }
            $zip->saveAsFile($fileName);
        } catch (Throwable $e) {
            throw new Exception('Unable to package zip file', 0, ['msg' => $e->getMessage(), 'file' => $fileName]);
        } finally {
            $zip->close();
        }
        if (file_exists($fileName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 递归创建目录
     * @param string $dir 目录路径
     * @return bool
     */
    public static function mkdir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return mkdir($dir, 0755, true);
        }
        return false;
    }
}