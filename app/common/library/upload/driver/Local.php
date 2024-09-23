<?php

namespace app\common\library\upload\driver;

use ba\Filesystem;
use think\facade\Config;
use think\file\UploadedFile;
use think\exception\FileException;
use app\common\library\upload\Driver;

/**
 * 上传到本地磁盘的驱动
 * @see Driver
 */
class Local extends Driver
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = Config::get('filesystem.disks.public');
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 保存文件
     * @param UploadedFile $file
     * @param string       $saveName
     * @return bool
     */
    public function save(UploadedFile $file, string $saveName): bool
    {
        $savePathInfo = pathinfo($saveName);
        $saveFullPath = $this->getFullPath($saveName);

        // cgi 直接 move
        if (request()->isCgi()) {
            $file->move($saveFullPath, $savePathInfo['basename']);
            return true;
        }

        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });

        // 建立文件夹
        if (!is_dir($saveFullPath) && !mkdir($saveFullPath, 0755, true)) {
            restore_error_handler();
            throw new FileException(sprintf('Unable to create the "%s" directory (%s)', $saveFullPath, strip_tags($error)));
        }

        // cli 使用 rename
        $saveName = $this->getFullPath($saveName, true);
        if (!rename($file->getPathname(), $saveName)) {
            restore_error_handler();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $file->getPathname(), $saveName, strip_tags($error)));
        }

        restore_error_handler();
        @chmod($saveName, 0666 & ~umask());
        return true;
    }

    /**
     * 删除文件
     * @param string $saveName
     * @return bool
     */
    public function delete(string $saveName): bool
    {
        $saveFullName = $this->getFullPath($saveName, true);
        if ($this->exists($saveFullName)) {
            @unlink($saveFullName);
        }
        Filesystem::delEmptyDir(dirname($saveFullName));
        return true;
    }

    /**
     * 获取资源 URL 地址
     * @param string      $saveName 资源保存名称
     * @param string|bool $domain   是否携带域名 或者直接传入域名
     * @param string      $default  默认值
     * @return string
     */
    public function url(string $saveName, string|bool $domain = true, string $default = ''): string
    {
        $saveName = $this->clearRootPath($saveName);

        if ($domain === true) {
            $domain = '//' . request()->host();
        } elseif ($domain === false) {
            $domain = '';
        }

        $saveName = $saveName ?: $default;
        if (!$saveName) return $domain;

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $saveName) || preg_match($regex, $saveName) || $domain === false) {
            return $saveName;
        }
        return str_replace('\\', '/', $domain . $saveName);
    }

    /**
     * 文件是否存在
     * @param string $saveName
     * @return bool
     */
    public function exists(string $saveName): bool
    {
        $saveFullName = $this->getFullPath($saveName, true);
        return file_exists($saveFullName);
    }

    /**
     * 获取文件的完整存储路径
     * @param string $saveName
     * @param bool   $baseName 是否包含文件名
     * @return string
     */
    public function getFullPath(string $saveName, bool $baseName = false): string
    {
        $savePathInfo = pathinfo($saveName);
        $root         = $this->getRootPath();
        $dirName      = $savePathInfo['dirname'] . '/';

        // 以 root 路径开始时单独返回，避免重复调用此方法时造成 $dirName 的错误拼接
        if (str_starts_with($saveName, $root)) {
            return Filesystem::fsFit($baseName || !isset($savePathInfo['extension']) ? $saveName : $dirName);
        }

        return Filesystem::fsFit($root . $dirName . ($baseName ? $savePathInfo['basename'] : ''));
    }

    public function clearRootPath(string $saveName): string
    {
        return str_replace($this->getRootPath(), '', Filesystem::fsFit($saveName));
    }

    public function getRootPath(): string
    {
        return Filesystem::fsFit(str_replace($this->options['url'], '', $this->options['root']));
    }
}