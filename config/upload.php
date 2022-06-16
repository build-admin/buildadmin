<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 上传Url
    'url'      => 'api/ajax/upload',
    // cdn地址
    'cdn'      => '',
    // 文件保存格式化方法
    'savename' => '/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}',
    // 最大上传
    'maxsize'  => '10mb',
    // 文件格式限制
    'mimetype' => 'jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,pdf,txt',
];