<?php

namespace ba;

use think\Exception;

/**
 * 依赖管理
 */
class Depends
{
    /**
     * 类型
     * @value npm | composer
     */
    protected $type = '';

    /**
     * json 文件完整路径
     */
    protected $json = null;

    /**
     * json 文件内容
     */
    protected $jsonContent = null;

    public function __construct(string $json, string $type = 'npm')
    {
        $this->json = $json;
        $this->type = $type;
    }

    /**
     * 获取 json 文件内容
     * @param bool $realTime 获取实时内容
     * @throws Exception
     */
    public function getContent(bool $realTime = false)
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
     * @throws Exception
     */
    public function setContent(array $content = [])
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
     * @throws Exception
     */
    public function getDepends(bool $devEnv = false)
    {
        try {
            $content = $this->getContent();
        } catch (Exception $e) {
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
     * @throws Exception
     */
    public function hasDepend(string $name, bool $devEnv = false)
    {
        $depends = $this->getDepends($devEnv);
        return $depends[$name] ?? false;
    }

    /**
     * 添加依赖
     * @param array $depends 要添加的依赖数组["xxx" => ">=7.1.0",]
     * @param bool  $devEnv  是否添加为开发环境依赖
     * @param bool  $cover   覆盖模式
     * @throws Exception
     */
    public function addDepends(array $depends, bool $devEnv = false, bool $cover = false)
    {
        $content = $this->getContent(true);
        $dKey    = $devEnv ? ($this->type == 'npm' ? 'devDependencies' : 'require-dev') : ($this->type == 'npm' ? 'dependencies' : 'require');
        if (!$cover) {
            foreach ($depends as $key => $item) {
                if (isset($content[$dKey][$key])) {
                    throw new Exception($key . ' dependencie already exists!');
                }
            }
        }
        $content[$dKey] = array_merge($content[$dKey], $depends);
        $this->setContent($content);
    }

    /**
     * 删除依赖
     * @param array $depends 要删除的依赖数组["php", "w7corp/easywechat"]
     * @param bool  $devEnv  是否为开发环境删除依赖
     * @throws Exception
     */
    public function removeDepends(array $depends, bool $devEnv = false)
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