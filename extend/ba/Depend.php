<?php

namespace ba;

use think\Exception;

/**
 * 依赖管理
 */
class Depend
{
    /**
     * @var string package.json 文件位置
     */
    protected $npm = null;

    /**
     * @var string composer.json 文件位置
     */
    protected $composer = null;

    protected $composerContent = null;

    protected $npmContent = null;

    public function __construct()
    {
        $this->npm      = root_path() . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'package.json';
        $this->composer = root_path() . DIRECTORY_SEPARATOR . 'composer.json';
    }

    /**
     * 获取 composer.json 内容
     * @param bool $realTime 获取实时内容
     * @throws Exception
     */
    public function getComposer(bool $realTime = false)
    {
        if (!file_exists($this->composer)) {
            throw new Exception('composer.json file does not exist!');
        }
        if ($this->composerContent && !$realTime) return $this->composerContent;
        $composerContent       = @file_get_contents($this->composer);
        $this->composerContent = json_decode($composerContent, true);
        if (!$this->composerContent) {
            throw new Exception('composer.json file read failure!');
        }
        return $this->composerContent;
    }

    /**
     * 获取 composer 依赖
     * @param bool $devEnv 是否是获取开发环境依赖
     * @throws Exception
     */
    public function getComposerRequire(bool $devEnv = false)
    {
        $composerContent = $this->getComposer();
        return $devEnv ? $composerContent['require-dev'] : $composerContent['require'];
    }

    /**
     * 是否存在某个 composer 依赖
     * @param string $requireName 依赖名称
     * @param bool   $devEnv      是否是获取开发环境依赖
     * @throws Exception
     */
    public function hasComposerRequire(string $requireName, bool $devEnv = false)
    {
        $require = $this->getComposerRequire($devEnv);
        return $require[$requireName] ?? false;
    }

    /**
     * 添加 composer 依赖
     * @param array $require 要添加的依赖数组["php" => ">=7.1.0",]
     * @param bool  $devEnv  是否添加为开发环境依赖
     * @throws Exception
     */
    public function addComposerRequire(array $require, bool $devEnv = false, bool $cover = false)
    {
        $composerContent = $this->getComposer(true);
        if ($devEnv) {
            $composerRequire                = $composerContent['require-dev'];
            $composerContent['require-dev'] = array_merge($composerContent['require-dev'], $require);
        } else {
            $composerRequire            = $composerContent['require'];
            $composerContent['require'] = array_merge($composerContent['require'], $require);
        }
        if (!$cover) {
            foreach ($require as $key => $item) {
                if (isset($composerRequire[$key])) {
                    throw new Exception($key . ' require already exists!');
                }
            }
        }
        $this->setComposer($composerContent);
    }

    /**
     * 设置 composer.json 内容
     * @param array $content
     * @throws Exception
     */
    public function setComposer(array $content)
    {
        if (!isset($content['require']) || !isset($content['name'])) {
            throw new Exception('composer.json file content is incomplete');
        }
        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $result  = @file_put_contents($this->composer, $content . PHP_EOL);
        if (!$result) {
            throw new Exception('File has no write permission:composer.json');
        }
    }

    /**
     * 获取 package.json 内容
     * @param bool $realTime 获取实时内容
     * @throws Exception
     */
    public function getNpm(bool $realTime = false)
    {
        if (!file_exists($this->npm)) {
            throw new Exception(DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'package.json file does not exist!');
        }
        if ($this->npmContent && !$realTime) return $this->npmContent;
        $npmContent       = @file_get_contents($this->npm);
        $this->npmContent = json_decode($npmContent, true);
        if (!$this->npmContent) {
            throw new Exception(DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'package.json file read failure!');
        }
        return $this->npmContent;
    }

    /**
     * 获取 npm 依赖
     * @param bool $devEnv 是否是获取开发环境依赖
     * @throws Exception
     */
    public function getNpmDependencies(bool $devEnv = false)
    {
        $npmContent = $this->getNpm();
        return $devEnv ? $npmContent['devDependencies'] : $npmContent['dependencies'];
    }

    /**
     * 是否存在某个 npm 依赖
     * @param string $dependenciesName 依赖名称
     * @param bool   $devEnv           是否是获取开发环境依赖
     * @throws Exception
     */
    public function hasNpmDependencies(string $dependenciesName, bool $devEnv = false)
    {
        $dependencies = $this->getNpmDependencies($devEnv);
        return $dependencies[$dependenciesName] ?? false;
    }

    /**
     * 添加 npm 依赖
     * @param array $dependencies 要添加的依赖数组["xxx" => ">=7.1.0",]
     * @param bool  $devEnv       是否添加为开发环境依赖
     * @throws Exception
     */
    public function addNpmDependencies(array $dependencies, bool $devEnv = false, bool $cover = false)
    {
        $npmContent = $this->getNpm(true);
        if ($devEnv) {
            $npmDependencies               = $npmContent['devDependencies'];
            $npmContent['devDependencies'] = array_merge($npmContent['devDependencies'], $dependencies);
        } else {
            $npmDependencies            = $npmContent['dependencies'];
            $npmContent['dependencies'] = array_merge($npmContent['dependencies'], $dependencies);
        }
        if (!$cover) {
            foreach ($dependencies as $key => $item) {
                if (isset($npmDependencies[$key])) {
                    throw new Exception($key . ' dependencie already exists!');
                }
            }
        }
        $this->setNpm($npmContent);
    }

    /**
     * 设置 package.json 内容
     * @param array $content
     * @throws Exception
     */
    public function setNpm(array $content)
    {
        if (!isset($content['dependencies']) || !isset($content['name'])) {
            throw new Exception('package.json file content is incomplete');
        }
        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $result  = @file_put_contents($this->npm, $content . PHP_EOL);
        if (!$result) {
            throw new Exception('File has no write permission:package.json');
        }
    }
}