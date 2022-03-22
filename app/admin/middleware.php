<?php
return [
    \app\common\middleware\AllowCrossDomain::class,
    \app\common\middleware\AdminLog::class,
    \think\middleware\LoadLangPack::class,
];
