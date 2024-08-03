<?php

namespace app\common\library\upload;

use think\file\UploadedFile;

/**
 * 上传驱动抽象类
 */
abstract class Driver
{
    /**
     * @var array 配置数据
     */
    protected array $options = [];

    /**
     * 保存文件
     * @param UploadedFile $file
     * @param string       $saveName
     * @return bool
     */
    abstract public function save(UploadedFile $file, string $saveName): bool;

    /**
     * 删除文件
     * @param string $saveName
     * @return bool
     */
    abstract public function delete(string $saveName): bool;

    /**
     * 获取资源 URL 地址；
     * @param string      $saveName 资源保存名称
     * @param string|bool $domain   是否携带域名 或者直接传入域名
     * @param string      $default  默认值
     * @return string
     */
    abstract public function url(string $saveName, string|bool $domain = true, string $default = ''): string;

    /**
     * 文件是否存在
     * @param string $saveName
     * @return bool
     */
    abstract public function exists(string $saveName): bool;
}