<?php

namespace ba;

use Throwable;
use think\Exception;

/**
 * 依赖管理
 */
class Depends
{
    /**
     * json 文件内容
     * @var array
     */
    protected array $jsonContent = [];

    public function __construct(protected string $json, protected string $type = 'npm')
    {

    }

    /**
     * 获取 json 文件内容
     * @param bool $realTime 获取实时内容
     * @return array
     * @throws Throwable
     */
    public function getContent(bool $realTime = false): array
    {
        if (!file_exists($this->json)) {
            throw new Exception($this->json . ' file does not exist!');
        }
        if ($this->jsonContent && !$realTime) return $this->jsonContent;
        $content           = @file_get_contents($this->json);
        $this->jsonContent = json_decode($content, true);
        if (!$this->jsonContent) {
            throw new Exception($this->json . ' file read failure!');
        }
        return $this->jsonContent;
    }

    /**
     * 设置 json 文件内容
     * @param array $content
     * @throws Throwable
     */
    public function setContent(array $content = []): void
    {
        if (!$content) $content = $this->jsonContent;
        if (!isset($content['name'])) {
            throw new Exception('Depend content file content is incomplete');
        }
        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $result  = @file_put_contents($this->json, $content . PHP_EOL);
        if (!$result) {
            throw new Exception('File has no write permission:' . $this->json);
        }
    }

    /**
     * 获取依赖项
     * @param bool $devEnv 是否是获取开发环境依赖
     * @return array
     * @throws Throwable
     */
    public function getDepends(bool $devEnv = false): array
    {
        try {
            $content = $this->getContent();
        } catch (Throwable) {
            return [];
        }

        if ($this->type == 'npm') {
            return $devEnv ? $content['devDependencies'] : $content['dependencies'];
        } else {
            return $devEnv ? $content['require-dev'] : $content['require'];
        }
    }

    /**
     * 是否存在某个依赖
     * @param string $name   依赖名称
     * @param bool   $devEnv 是否是获取开发环境依赖
     * @return bool|string false或者依赖版本号
     * @throws Throwable
     */
    public function hasDepend(string $name, bool $devEnv = false): bool|string
    {
        $depends = $this->getDepends($devEnv);
        return $depends[$name] ?? false;
    }

    /**
     * 添加依赖
     * @param array $depends 要添加的依赖数组["xxx" => ">=7.1.0",]
     * @param bool  $devEnv  是否添加为开发环境依赖
     * @param bool  $cover   覆盖模式
     * @return void
     * @throws Throwable
     */
    public function addDepends(array $depends, bool $devEnv = false, bool $cover = false): void
    {
        $content = $this->getContent(true);
        $dKey    = $devEnv ? ($this->type == 'npm' ? 'devDependencies' : 'require-dev') : ($this->type == 'npm' ? 'dependencies' : 'require');
        if (!$cover) {
            foreach ($depends as $key => $item) {
                if (isset($content[$dKey][$key])) {
                    throw new Exception($key . ' depend already exists!');
                }
            }
        }
        $content[$dKey] = array_merge($content[$dKey], $depends);
        $this->setContent($content);
    }

    /**
     * 删除依赖
     * @param array $depends 要删除的依赖数组["php", "w7corp/easyWechat"]
     * @param bool  $devEnv  是否为开发环境删除依赖
     * @return void
     * @throws Throwable
     */
    public function removeDepends(array $depends, bool $devEnv = false): void
    {
        $content = $this->getContent(true);
        $dKey    = $devEnv ? ($this->type == 'npm' ? 'devDependencies' : 'require-dev') : ($this->type == 'npm' ? 'dependencies' : 'require');
        foreach ($depends as $item) {
            if (isset($content[$dKey][$item])) {
                unset($content[$dKey][$item]);
            }
        }
        $this->setContent($content);
    }
}