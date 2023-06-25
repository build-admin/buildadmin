<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
// | 妙码生花在 2022-2-26 进行修订，通过Mysql保存验证码而不是Session以更好的支持API访问
// | 使用Cache不能清理过期验证码，且一旦执行清理缓存操作，验证码将失效
// +----------------------------------------------------------------------

namespace ba;

use GdImage;
use Throwable;
use think\Response;
use think\facade\Db;

/**
 * 验证码类（图形验证码、继续流程验证码）
 * @property string $seKey    验证码加密密钥
 * @property string $codeSet  验证码字符集合
 * @property int    $expire   验证码过期时间（s）
 * @property bool   $useZh    使用中文验证码
 * @property string $zhSet    中文验证码字符串
 * @property bool   $useImgBg 使用背景图片
 * @property int    $fontSize 验证码字体大小(px)
 * @property bool   $useCurve 是否画混淆曲线
 * @property bool   $useNoise 是否添加杂点
 * @property int    $imageH   验证码图片高度
 * @property int    $imageW   验证码图片宽度
 * @property int    $length   验证码位数
 * @property string $fontTtf  验证码字体，不设置随机获取
 * @property array  $bg       背景颜色
 * @property bool   $reset    验证成功后是否重置
 */
class Captcha
{
    protected array $config = [
        // 验证码加密密钥
        'seKey'    => 'BuildAdmin',
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码过期时间（s）
        'expire'   => 600,
        // 使用中文验证码
        'useZh'    => false,
        // 中文验证码字符串
        'zhSet'    => '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借',
        // 使用背景图片
        'useImgBg' => false,
        // 验证码字体大小(px)
        'fontSize' => 25,
        // 是否画混淆曲线
        'useCurve' => true,
        // 是否添加杂点
        'useNoise' => true,
        // 验证码图片高度
        'imageH'   => 0,
        // 验证码图片宽度
        'imageW'   => 0,
        // 验证码位数
        'length'   => 4,
        // 验证码字体，不设置随机获取
        'fontTtf'  => '',
        // 背景颜色
        'bg'       => [243, 251, 254],
        // 验证成功后是否重置
        'reset'    => true,
    ];

    /**
     * 验证码图片实例
     * @var GdImage|resource|null
     */
    private $image = null;

    /**
     * 验证码字体颜色
     * @var bool|int|null
     */
    private bool|int|null $color = null;

    /**
     * 架构方法 设置参数
     * @param array $config 配置参数
     * @throws Throwable
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        // 清理过期的验证码
        Db::name('captcha')
            ->where('expire_time', '<', time())
            ->delete();
    }

    /**
     * 使用 $this->name 获取配置
     * @param string $name 配置名称
     * @return mixed    配置值
     */
    public function __get(string $name): mixed
    {
        return $this->config[$name];
    }

    /**
     * 设置验证码配置
     * @param string $name  配置名称
     * @param mixed  $value 配置值
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 检查配置
     * @param string $name 配置名称
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->config[$name]);
    }

    /**
     * 验证验证码是否正确
     * @param string $code 用户验证码
     * @param string $id   验证码标识
     * @return bool 用户验证码是否正确
     * @throws Throwable
     */
    public function check(string $code, string $id): bool
    {
        $key    = $this->authCode($this->seKey, $id);
        $seCode = Db::name('captcha')->where('key', $key)->find();

        // 验证码为空
        if (empty($code) || empty($seCode)) {
            return false;
        }

        // 验证码过期
        if (time() > $seCode['expire_time']) {
            Db::name('captcha')->where('key', $key)->delete();
            return false;
        }

        if ($this->authCode(strtoupper($code), $id) == $seCode['code']) {
            $this->reset && Db::name('captcha')->where('key', $key)->delete();
            return true;
        }

        return false;
    }

    /**
     * 创建一个逻辑验证码可供后续验证（非图形）
     * @param string      $id      验证码标识
     * @param string|bool $captcha 验证码，不传递则自动生成
     * @return string 生成的验证码，发送出去或做它用...
     * @throws Throwable
     */
    public function create(string $id, string|bool $captcha = false): string
    {
        $nowTime     = time();
        $key         = $this->authCode($this->seKey, $id);
        $captchaTemp = Db::name('captcha')->where('key', $key)->find();
        if ($captchaTemp) {
            // 重复的为同一标识创建验证码
            Db::name('captcha')->where('key', $key)->delete();
        }
        $captcha = $this->generate($captcha);
        $code    = $this->authCode($captcha, $id);
        Db::name('captcha')
            ->insert([
                'key'         => $key,
                'code'        => $code,
                'captcha'     => $captcha,
                'create_time' => $nowTime,
                'expire_time' => $nowTime + $this->expire
            ]);
        return $captcha;
    }

    /**
     * 获取验证码数据
     * @param string $id 验证码标识
     * @return array
     * @throws Throwable
     */
    public function getCaptchaData(string $id): array
    {
        $key    = $this->authCode($this->seKey, $id);
        $seCode = Db::name('captcha')->where('key', $key)->find();
        return $seCode ?: [];
    }

    /**
     * 输出图形验证码并把验证码的值保存的Mysql中
     * @param string $id 要生成验证码的标识
     * @return Response
     * @throws Throwable
     */
    public function entry(string $id): Response
    {
        $nowTime = time();
        // 图片宽(px)
        $this->imageW || $this->imageW = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
        // 图片高(px)
        $this->imageH || $this->imageH = $this->fontSize * 2.5;
        // 建立一幅 $this->imageW x $this->imageH 的图像
        $this->image = imagecreate($this->imageW, $this->imageH);
        // 设置背景
        imagecolorallocate($this->image, $this->bg[0], $this->bg[1], $this->bg[2]);

        // 验证码字体随机颜色
        $this->color = imagecolorallocate($this->image, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        // 验证码使用随机字体
        $ttfPath = public_path() . 'static' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . ($this->useZh ? 'zhttfs' : 'ttfs') . DIRECTORY_SEPARATOR;

        if (empty($this->fontTtf)) {
            $dir      = dir($ttfPath);
            $ttfFiles = [];
            while (false !== ($file = $dir->read())) {
                if ('.' != $file[0] && str_ends_with($file, '.ttf')) {
                    $ttfFiles[] = $file;
                }
            }
            $dir->close();
            $this->fontTtf = $ttfFiles[array_rand($ttfFiles)];
        }
        $this->fontTtf = $ttfPath . $this->fontTtf;

        if ($this->useImgBg) {
            $this->background();
        }

        if ($this->useNoise) {
            // 绘杂点
            $this->writeNoise();
        }
        if ($this->useCurve) {
            // 绘干扰线
            $this->writeCurve();
        }

        $key     = $this->authCode($this->seKey, $id);
        $captcha = Db::name('captcha')->where('key', $key)->find();

        // 绘验证码
        if ($captcha && $nowTime <= $captcha['expire_time']) {
            $this->writeText($captcha['captcha']);
        } else {
            $captcha = $this->writeText();

            // 保存验证码
            $code = $this->authCode(strtoupper(implode('', $captcha)), $id);
            Db::name('captcha')->insert([
                'key'         => $key,
                'code'        => $code,
                'captcha'     => strtoupper(implode('', $captcha)),
                'create_time' => $nowTime,
                'expire_time' => $nowTime + $this->expire
            ]);
        }

        ob_start();
        // 输出图像
        imagepng($this->image);
        $content = ob_get_clean();
        imagedestroy($this->image);

        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/png');
    }

    /**
     * 绘验证码
     * @param string $captcha 验证码
     * @return array|string 验证码
     */
    private function writeText(string $captcha = ''): array|string
    {
        $code   = []; // 验证码
        $codeNX = 0; // 验证码第N个字符的左边距
        if ($this->useZh) {
            // 中文验证码
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = $captcha ? $captcha[$i] : iconv_substr($this->zhSet, floor(mt_rand(0, mb_strlen($this->zhSet, 'utf-8') - 1)), 1, 'utf-8');
                imagettftext($this->image, $this->fontSize, mt_rand(-40, 40), $this->fontSize * ($i + 1) * 1.5, $this->fontSize + mt_rand(10, 20), (int)$this->color, $this->fontTtf, $code[$i]);
            }
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = $captcha ? $captcha[$i] : $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
                $codeNX   += mt_rand((int)($this->fontSize * 1.2), (int)($this->fontSize * 1.6));
                imagettftext($this->image, $this->fontSize, mt_rand(-40, 40), $codeNX, (int)($this->fontSize * 1.6), (int)$this->color, $this->fontTtf, $code[$i]);
            }
        }
        return $captcha ?: $code;
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     * 正弦型函数解析式：y=Asin(ωx+φ)+b
     * 各常数值对函数图像的影响：
     * A：决定峰值（即纵向拉伸压缩的倍数）
     * b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     * φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     * ω：决定周期（最小正周期T=2π/∣ω∣）
     */
    private function writeCurve(): void
    {
        $py = 0;

        // 曲线前部分
        $A = mt_rand(1, $this->imageH / 2); // 振幅
        $b = mt_rand(-$this->imageH / 4, $this->imageH / 4); // Y轴方向偏移量
        $f = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($this->imageW / 2, $this->imageW * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int)($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->image, $px + $i, $py + $i, (int)$this->color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, $this->imageH / 2); // 振幅
        $f   = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T   = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
        $px1 = $px2;
        $px2 = $this->imageW;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int)($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->image, $px + $i, $py + $i, (int)$this->color);
                    $i--;
                }
            }
        }
    }

    /**
     * 绘杂点，往图片上写不同颜色的字母或数字
     */
    private function writeNoise(): void
    {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                // 绘制
                imagestring($this->image, 5, mt_rand(-10, $this->imageW), mt_rand(-10, $this->imageH), $codeSet[mt_rand(0, 29)], $noiseColor);
            }
        }
    }

    /**
     * 绘制背景图片
     *
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     */
    private function background(): void
    {
        $path = Filesystem::fsFit(public_path() . 'static/images/captcha/image/');
        $dir  = dir($path);

        $bgs = [];
        while (false !== ($file = $dir->read())) {
            if ('.' != $file[0] && str_ends_with($file, '.jpg')) {
                $bgs[] = $path . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        list($width, $height) = @getimagesize($gb);
        // Resample
        $bgImage = @imagecreatefromjpeg($gb);
        @imagecopyresampled($this->image, $bgImage, 0, 0, 0, 0, $this->imageW, $this->imageH, $width, $height);
        @imagedestroy($bgImage);
    }


    /**
     * 加密验证码
     * @param string $str 验证码字符串
     * @param string $id  验证码标识
     */
    private function authCode(string $str, string $id): string
    {
        $key = substr(md5($this->seKey), 5, 8);
        $str = substr(md5($str), 8, 10);
        return md5($key . $str . $id);
    }

    /**
     * 生成验证码随机字符
     * @param bool|string $captcha
     * @return string
     */
    private function generate(bool|string $captcha = false): string
    {
        $code = []; // 验证码
        if ($this->useZh) {
            // 中文验证码
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = $captcha ? $captcha[$i] : iconv_substr($this->zhSet, floor(mt_rand(0, mb_strlen($this->zhSet, 'utf-8') - 1)), 1, 'utf-8');
            }
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $code[$i] = $captcha ? $captcha[$i] : $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
            }
        }
        return $captcha ?: strtoupper(implode('', $code));
    }
}