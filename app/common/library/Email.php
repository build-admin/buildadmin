<?php

namespace app\common\library;

use Throwable;
use think\facade\Lang;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件类
 * 继承PHPMailer并初始化好了站点系统配置中的邮件配置信息
 */
class Email extends PHPMailer
{
    /**
     * 是否已在管理后台配置好邮件服务
     * @var bool
     */
    public bool $configured = false;

    /**
     * 默认配置
     * @var array
     */
    public array $options = [
        'charset' => 'utf-8', //编码格式
        'debug'   => true, //调式模式
        'lang'    => 'zh_cn',
    ];

    /**
     * 构造函数
     * @param array $options
     * @throws Throwable
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        parent::__construct($this->options['debug']);
        $langSet = Lang::getLangSet();
        if ($langSet == 'zh-cn' || !$langSet) $langSet = 'zh_cn';
        $this->options['lang'] = $this->options['lang'] ?: $langSet;

        $this->setLanguage($this->options['lang'], root_path() . 'vendor' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR);
        $this->CharSet = $this->options['charset'];

        $sysMailConfig    = get_sys_config('', 'mail');
        $this->configured = true;
        foreach ($sysMailConfig as $item) {
            if (!$item) {
                $this->configured = false;
            }
        }
        if ($this->configured) {
            $this->Host       = $sysMailConfig['smtp_server'];
            $this->SMTPAuth   = true;
            $this->Username   = $sysMailConfig['smtp_user'];
            $this->Password   = $sysMailConfig['smtp_pass'];
            $this->SMTPSecure = $sysMailConfig['smtp_verification'] == 'SSL' ? self::ENCRYPTION_SMTPS : self::ENCRYPTION_STARTTLS;
            $this->Port       = $sysMailConfig['smtp_port'];

            $this->setFrom($sysMailConfig['smtp_sender_mail'], $sysMailConfig['smtp_user']);
        }
    }

    public function setSubject($subject): void
    {
        $this->Subject = "=?utf-8?B?" . base64_encode($subject) . "?=";
    }
}