<?php
// +----------------------------------------------------------------------
// | BuildAdmin上传设置
// +----------------------------------------------------------------------

return [
    // 最大上传
    'max_size'           => '10mb',
    // 文件保存格式化方法:topic=存储子目录,fileName=文件名前15个字符
    'save_name'          => '/storage/{topic}/{year}{mon}{day}/{fileName}{fileSha1}{.suffix}',

    /**
     * 上传文件的后缀和 MIME类型 白名单
     * 0. 永远使用最少配置
     * 1. 此处不支持通配符
     * 2. 千万不要允许 php,php5,.htaccess,.user.ini 等可执行或配置文件
     * 3. 允许 pdf,ppt,docx 等可能含有脚本的文件时，请先从服务器配置此类文件直接下载而不是预览
     */
    'allowed_suffixes'   => 'jpg,png,bmp,jpeg,gif,webp,zip,rar,wav,mp4,mp3',
    'allowed_mime_types' => [],
];