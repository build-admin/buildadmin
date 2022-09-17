<?php
// +----------------------------------------------------------------------
// | BuildAdmin设置
// +----------------------------------------------------------------------

return [
    // 最大上传
    'maxsize'  => '10mb',
    // 文件保存格式化方法
    'savename' => '/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}',
    // 文件格式限制
    'mimetype' => 'jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,pdf,txt',
];