<?php

namespace app\common\library;

use Throwable;
use ba\Random;
use ba\Filesystem;
use think\Exception;
use think\helper\Str;
use think\facade\Config;
use think\facade\Validate;
use think\file\UploadedFile;
use InvalidArgumentException;
use think\validate\ValidateRule;
use app\common\model\Attachment;
use app\common\library\upload\Driver;

/**
 * 上传
 */
class Upload
{
    /**
     * 上传配置
     */
    protected array $config = [];

    /**
     * 被上传文件
     */
    protected ?UploadedFile $file = null;

    /**
     * 是否是图片
     */
    protected bool $isImage = false;

    /**
     * 文件信息
     */
    protected array $fileInfo;

    /**
     * 上传驱动
     */
    protected array $driver = [
        'name'      => 'local', // 默认驱动:local=本地
        'handler'   => [], // 驱动句柄
        'namespace' => '\\app\\common\\library\\upload\\driver\\', // 驱动类的命名空间
    ];

    /**
     * 存储子目录
     */
    protected string $topic = 'default';

    /**
     * 构造方法
     * @param ?UploadedFile $file   上传的文件
     * @param array         $config 配置
     * @throws Throwable
     */
    public function __construct(?UploadedFile $file = null, array $config = [])
    {
        $upload       = Config::get('upload');
        $this->config = array_merge($upload, $config);

        if ($file) {
            $this->setFile($file);
        }
    }

    /**
     * 设置上传文件
     * @param ?UploadedFile $file
     * @return Upload
     * @throws Throwable
     */
    public function setFile(?UploadedFile $file): Upload
    {
        if (empty($file)) {
            throw new Exception(__('No files were uploaded'));
        }

        $suffix             = strtolower($file->extension());
        $suffix             = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        $fileInfo['suffix'] = $suffix;
        $fileInfo['type']   = $file->getMime();
        $fileInfo['size']   = $file->getSize();
        $fileInfo['name']   = $file->getOriginalName();
        $fileInfo['sha1']   = $file->sha1();

        $this->file     = $file;
        $this->fileInfo = $fileInfo;
        return $this;
    }

    /**
     * 设置上传驱动
     */
    public function setDriver(string $driver): Upload
    {
        $this->driver['name'] = $driver;
        return $this;
    }

    /**
     * 获取上传驱动句柄
     * @param ?string $driver           驱动名称
     * @param bool    $noDriveException 找不到驱动是否抛出异常
     * @return bool|Driver
     */
    public function getDriver(?string $driver = null, bool $noDriveException = true): bool|Driver
    {
        if (is_null($driver)) {
            $driver = $this->driver['name'];
        }
        if (!isset($this->driver['handler'][$driver])) {
            $class = $this->resolveDriverClass($driver);
            if ($class) {
                $this->driver['handler'][$driver] = new $class();
            } elseif ($noDriveException) {
                throw new InvalidArgumentException(__('Driver %s not supported', [$driver]));
            }
        }
        return $this->driver['handler'][$driver] ?? false;
    }

    /**
     * 获取驱动类
     */
    protected function resolveDriverClass(string $driver): bool|string
    {
        if ($this->driver['namespace'] || str_contains($driver, '\\')) {
            $class = str_contains($driver, '\\') ? $driver : $this->driver['namespace'] . Str::studly($driver);
            if (class_exists($class)) {
                return $class;
            }
        }
        return false;
    }

    /**
     * 设置存储子目录
     */
    public function setTopic(string $topic): Upload
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * 检查是否是图片并设置好相关属性
     * @return bool
     * @throws Throwable
     */
    protected function checkIsImage(): bool
    {
        if (in_array($this->fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($this->fileInfo['suffix'], ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($this->file->getPathname());
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                throw new Exception(__('The uploaded image file is not a valid image'));
            }
            $this->fileInfo['width']  = $imgInfo[0];
            $this->fileInfo['height'] = $imgInfo[1];
            $this->isImage            = true;
            return true;
        }
        return false;
    }

    /**
     * 上传的文件是否为图片
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->isImage;
    }

    /**
     * 获取文件后缀
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->fileInfo['suffix'] ?: 'file';
    }

    /**
     * 获取文件保存路径和名称
     * @param ?string $saveName
     * @param ?string $filename
     * @param ?string $sha1
     * @return string
     */
    public function getSaveName(?string $saveName = null, ?string $filename = null, ?string $sha1 = null): string
    {
        if ($filename) {
            $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        } else {
            $suffix = $this->fileInfo['suffix'];
        }
        $filename   = $filename ?: $this->fileInfo['name'];
        $sha1       = $sha1 ?: $this->fileInfo['sha1'];
        $replaceArr = [
            '{topic}'    => $this->topic,
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::build(),
            '{random32}' => Random::build('alnum', 32),
            '{fileName}' => $this->getFileNameSubstr($filename, $suffix),
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{fileSha1}' => $sha1,
        ];
        $saveName   = $saveName ?: $this->config['save_name'];
        return Filesystem::fsFit(str_replace(array_keys($replaceArr), array_values($replaceArr), $saveName));
    }

    /**
     * 验证文件是否符合上传配置要求
     * @throws Throwable
     */
    public function validates(): void
    {
        if (empty($this->file)) {
            throw new Exception(__('No files have been uploaded or the file size exceeds the upload limit of the server'));
        }

        $size   = Filesystem::fileUnitToByte($this->config['max_size']);
        $mime   = $this->checkConfig($this->config['allowed_mime_types']);
        $suffix = $this->checkConfig($this->config['allowed_suffixes']);

        // 文件大小
        $fileValidateRule = ValidateRule::fileSize($size, __('The uploaded file is too large (%sMiB), Maximum file size:%sMiB', [
            round($this->fileInfo['size'] / pow(1024, 2), 2),
            round($size / pow(1024, 2), 2)
        ]));

        // 文件后缀
        if ($suffix) {
            $fileValidateRule->fileExt($suffix, __('The uploaded file format is not allowed'));
        }
        // 文件 MIME 类型
        if ($mime) {
            $fileValidateRule->fileMime($mime, __('The uploaded file format is not allowed'));
        }

        // 图片文件利用tp内置规则做一些额外检查
        if ($this->checkIsImage()) {
            $fileValidateRule->image("{$this->fileInfo['width']},{$this->fileInfo['height']},{$this->fileInfo['suffix']}", __('The uploaded image file is not a valid image'));
        }

        Validate::failException()
            ->rule([
                'file'   => $fileValidateRule,
                'topic'  => ValidateRule::is('alphaDash', __('Topic format error')),
                'driver' => ValidateRule::is('alphaDash', __('Driver %s not supported', [$this->driver['name']])),
            ])
            ->check([
                'file'   => $this->file,
                'topic'  => $this->topic,
                'driver' => $this->driver['name'],
            ]);
    }

    /**
     * 上传文件
     * @param ?string $saveName
     * @param int     $adminId
     * @param int     $userId
     * @return array
     * @throws Throwable
     */
    public function upload(?string $saveName = null, int $adminId = 0, int $userId = 0): array
    {
        $this->validates();

        $driver   = $this->getDriver();
        $saveName = $saveName ?: $this->getSaveName();
        $params   = [
            'topic'    => $this->topic,
            'admin_id' => $adminId,
            'user_id'  => $userId,
            'url'      => $driver->url($saveName, false),
            'width'    => $this->fileInfo['width'] ?? 0,
            'height'   => $this->fileInfo['height'] ?? 0,
            'name'     => $this->getFileNameSubstr($this->fileInfo['name'], $this->fileInfo['suffix'], 100) . ".{$this->fileInfo['suffix']}",
            'size'     => $this->fileInfo['size'],
            'mimetype' => $this->fileInfo['type'],
            'storage'  => $this->driver['name'],
            'sha1'     => $this->fileInfo['sha1']
        ];

        // 附件数据入库 - 不依赖模型新增前事件，确保入库前文件已经移动完成
        $attachment = Attachment::where('sha1', $params['sha1'])
            ->where('topic', $params['topic'])
            ->where('storage', $params['storage'])
            ->find();
        if ($attachment && $driver->exists($saveName)) {
            $attachment->quote++;
            $attachment->last_upload_time = time();
        } else {
            $driver->save($this->file, $saveName);
            $attachment = new Attachment();
            $attachment->data(array_filter($params));
        }
        $attachment->save();
        return $attachment->toArray();
    }

    /**
     * 获取文件名称字符串的子串
     */
    public function getFileNameSubstr(string $fileName, string $suffix, int $length = 15): string
    {
        // 对 $fileName 中不利于传输的字符串进行过滤
        $pattern  = "/[\s:@#?&\/=',+]+/u";
        $fileName = str_replace(".$suffix", '', $fileName);
        $fileName = preg_replace($pattern, '', $fileName);
        return mb_substr(htmlspecialchars(strip_tags($fileName)), 0, $length);
    }

    /**
     * 检查配置项，将 string 类型的配置转换为 array，并且将所有字母转换为小写
     */
    protected function checkConfig($configItem): array
    {
        if (is_array($configItem)) {
            return array_map('strtolower', $configItem);
        } else {
            return explode(',', strtolower($configItem));
        }
    }
}